<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Domains\FinancialTransaction;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\ForwardedPaymentData;
use App\Components\Locations\Models\LocationUser;
use App\Helpers\Decimal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ForwardedPaymentsService
 * Implements forwarding feature. Forwarding produce payment and transaction records and forward invoices funds
 * from one GL account to another.
 *
 * @package App\Components\Finance\Services
 */
class ForwardedPaymentsService implements ForwardedPaymentsServiceInterface
{
    /** @var \App\Components\Finance\Interfaces\GLAccountServiceInterface */
    private $glAccountService;

    /**
     * ForwardPaymentsService constructor.
     *
     * @param \App\Components\Finance\Interfaces\GLAccountServiceInterface $glAccountService
     */
    public function __construct(GLAccountServiceInterface $glAccountService)
    {
        $this->glAccountService = $glAccountService;
    }

    /**
     * {@inheritDoc}
     */
    public function getUnforwarded(int $locationId, array $invoicesIds = []): Collection
    {
        $markedAsFp = $this->getInvoicePaymentsMarkedAsForwardedByLocation($locationId, $invoicesIds);

        $forwardedInvoicePaymentsBefore = $this->getForwardedInvoicePaymentsBefore($invoicesIds);

        return $forwardedInvoicePaymentsBefore->isEmpty()
            ? $markedAsFp
            : $this->getUnforwardedIfForwardedPaymentsExist($markedAsFp, $forwardedInvoicePaymentsBefore);
    }

    /**
     * Returns forwarded invoice payments.
     *
     * @param array $invoicesIds Specified invoices identifiers.
     *
     * @return Collection|ForwardedPayment[]
     */
    private function getForwardedInvoicePaymentsBefore($invoicesIds = []): Collection
    {
        return ForwardedPaymentInvoice::query()
            ->selectRaw('MAX(forwarded_payment_id) as forwarded_payment_id, SUM(amount) as amount, invoice_id')
            ->when($invoicesIds, function (Builder $query) use ($invoicesIds) {
                return $query->whereIn('invoice_id', $invoicesIds);
            })
            ->groupBy('invoice_id')
            ->orderBy('forwarded_payment_id')
            ->get();
    }

    /**
     * Returns collection of invoices marked as forwarded (is_fp=true)
     *
     * @param int   $locationId
     *
     * @param array $invoicesIds
     *
     * @return \Illuminate\Support\Collection
     */
    private function getInvoicePaymentsMarkedAsForwardedByLocation(int $locationId, $invoicesIds = []): Collection
    {
        return Invoice::query()
            ->join('invoice_payment AS ip', 'invoices.id', '=', 'ip.invoice_id')
            ->where([
                'ip.is_fp'             => true,
                'invoices.location_id' => $locationId,
            ])
            ->when($invoicesIds, function (Builder $query) use ($invoicesIds) {
                return $query->whereIn('invoices.id', $invoicesIds);
            })
            ->orderBy('payment_id')
            ->get();
    }

    /**
     * Returns unforwarded invoice payments which have forwarded payment.
     * Invoice payments can be partial, so there are calculations with subtotal see integration tests for details.
     * Logic for partial payment
     *
     * forwarded_payment_invoices
     * f_p_i, invoice_id, amount
     * 1,1,20
     *
     * invoice_payment
     * payment_id, invoice_id, amount
     * 5,1,10
     * 6,1,10
     * 7,1,5
     *
     * @param Collection|InvoicePayment[]          $invoicePaymentsMarkedAsFp Invoices payments marked as forwarded
     *                                                                        (is_fp=true)
     * @param Collection|ForwardedPaymentInvoice[] $forwardedPaymentInvoice
     *
     * @return Collection|InvoicePayment[]
     */
    private function getUnforwardedIfForwardedPaymentsExist(
        Collection $invoicePaymentsMarkedAsFp,
        Collection $forwardedPaymentInvoice
    ): Collection {
        $unforwarded                = new Collection();
        $forwardedPaymentInvoiceIds = $forwardedPaymentInvoice->pluck('invoice_id')->toArray();

        foreach ($forwardedPaymentInvoice as $fpi) {
            $subTotal = 0;
            foreach ($invoicePaymentsMarkedAsFp as $ip) {
                if (!in_array($ip->invoice_id, $forwardedPaymentInvoiceIds, true)) {
                    $unforwarded->push($ip);
                    continue;
                }

                if ($fpi->invoice_id !== $ip->invoice_id) {
                    continue;
                }

                $subTotal += $ip->amount;
                if (bccomp($subTotal, $fpi->amount, 2) <= 0) {
                    continue;
                }
                $unforwarded->push($ip);
            }
        }

        return $unforwarded;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Throwable
     */
    public function forward(ForwardedPaymentData $data): Payment
    {
        $sourceAccount      = $this->getSourceAccount($data->getSourceGLAccountId());
        $destinationAccount = $this->glAccountService->getGLAccount($data->getDestinationGLAccountId());

        /** @var LocationUser $primaryLocation */
        $primaryLocation = LocationUser::query()
            ->where([
                'user_id' => $data->userId,
                'primary' => true,
            ])
            ->firstOrFail();

        $unforwardedInvoicePayment = $this->getUnforwarded($primaryLocation->location_id, $data->getInvoicesIds());

        $funds = $this->getFunds($sourceAccount, $unforwardedInvoicePayment);

        return DB::transaction(function () use (
            $sourceAccount,
            $destinationAccount,
            $funds,
            $unforwardedInvoicePayment,
            $data
        ) {
            $transactionId = $this->createTransaction($sourceAccount, $destinationAccount, $funds);

            $reference = sprintf(
                'Forward payment from HQ bank account [%s] to franchise GL account [%s] for invoices [%s].',
                $sourceAccount->name,
                $destinationAccount->name,
                implode(',', $unforwardedInvoicePayment->pluck('invoice_id')->toArray())
            );

            $payment = $this->createPayment($transactionId, $funds, $reference, $data->userId);

            $this->createForwardedPayment($payment->id, $data, $unforwardedInvoicePayment);

            return $payment;
        });
    }

    /**
     * From this account funds will be forwarded.
     *
     * @param int $glAccountId GL Account identifier.
     *
     * @return \App\Components\Finance\Models\GLAccount
     */
    private function getSourceAccount(int $glAccountId): GLAccount
    {
        $account = $this->glAccountService->getGLAccount($glAccountId);

        if (null === $account->bank_account_name) {
            throw new NotAllowedException(
                sprintf('Source account [%s] must be a bank account.', $account->name)
            );
        }

        return $account;
    }

    /**
     * Indicates whether the GL account balance is more than sum of amount invoices.
     *
     * @param GLAccount                                       $srcGLAccount
     * @param \Illuminate\Support\Collection|InvoicePayment[] $mapInvoiceIdAmount
     *
     * @return float
     */
    private function getFunds(GLAccount $srcGLAccount, Collection $mapInvoiceIdAmount): float
    {
        $srcBalance = $this->glAccountService->getAccountBalance($srcGLAccount->id);

        $funds = 0;
        foreach ($mapInvoiceIdAmount as $invoiceAmountItem) {
            $funds = bcadd($funds, $invoiceAmountItem->amount, 2);
        }

        if (Decimal::lte($funds, 0)) {
            throw new NotAllowedException('Incorrect funds value. It is less or equals to zero.');
        }

        $isFundsEnough = bccomp($srcBalance, $funds, 2) >= 0 && bccomp($srcBalance, 0, 2) > 0;

        if (!$isFundsEnough) {
            throw new NotAllowedException(
                sprintf('Source account [%s] does not have enough funds.', $srcGLAccount->name)
            );
        }

        return $funds;
    }

    /**
     * Creates transaction.
     *
     * @param GLAccount $sourceAccount    Source GL account (bank).
     * @param GLAccount $franchiseAccount Franchise GL account.
     * @param float     $funds            Funds for forwarding.
     *
     * @return int Transaction identifier.
     */
    private function createTransaction(GLAccount $sourceAccount, GLAccount $franchiseAccount, float $funds): int
    {
        $transaction = new FinancialTransaction($sourceAccount->accounting_organization_id);

        return $transaction->decrease($sourceAccount, $funds)
            ->increase($franchiseAccount, $funds)
            ->commit();
    }

    /**
     * Creates payment for transaction.
     *
     * @param int    $transactionId Transaction identifier.
     * @param float  $amount        Payment amount.
     * @param string $reference     Reference text.
     * @param null   $userId        User identifier who made forwarding.
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \Throwable
     */
    private function createPayment(int $transactionId, float $amount, string $reference, $userId = null): Payment
    {
        $payment                 = new Payment();
        $payment->amount         = $amount;
        $payment->paid_at        = Carbon::now();
        $payment->type           = PaymentTypes::FORWARDED;
        $payment->transaction_id = $transactionId;
        $payment->user_id        = $userId;
        $payment->reference      = $reference;
        $payment->saveOrFail();

        return $payment;
    }

    /**
     * Creates forwarded payment and attaches to invoices.
     *
     * @param int                         $paymentId                 Payment identifier.
     * @param ForwardedPaymentData        $data                      Forwarded payment data.
     * @param Collection|InvoicePayment[] $unforwardedInvoicePayment Unforwarded invoice payment.
     *
     * @return \App\Components\Finance\Models\ForwardedPayment
     *
     * @throws \Throwable
     */
    private function createForwardedPayment(
        int $paymentId,
        ForwardedPaymentData $data,
        Collection $unforwardedInvoicePayment
    ): ForwardedPayment {
        $forwardedPayment                       = new ForwardedPayment();
        $forwardedPayment->payment_id           = $paymentId;
        $forwardedPayment->remittance_reference = $data->getRemittanceReference();
        $forwardedPayment->transferred_at       = $data->getTransferredAt();
        $forwardedPayment->saveOrFail();

        $invoiceGroupedByAmount = [];

        foreach ($unforwardedInvoicePayment as $item) {
            if (!isset($invoiceGroupedByAmount[$item->invoice_id])) {
                $invoiceGroupedByAmount[$item->invoice_id] = $item->amount;
            } else {
                $invoiceGroupedByAmount[$item->invoice_id] += $item->amount;
            }
        }

        foreach ($invoiceGroupedByAmount as $invoiceId => $amount) {
            $forwardedPayment->invoice()->attach($invoiceId, ['amount' => $amount]);
        }

        return $forwardedPayment;
    }
}
