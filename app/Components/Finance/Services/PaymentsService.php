<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\CreditCardPaymentProcessor;
use App\Components\Finance\Domains\FinancialTransaction;
use App\Components\Finance\Enums\PaymentTypes;
use App\Components\Finance\Events\CreditCardPaymentProcessedEvent;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\PaymentsServiceInterface;
use App\Components\Finance\Models\CreditCardTransaction;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\CreatePaymentData;
use App\Components\Finance\Models\VO\PaymentReceipt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Class PaymentsService
 *
 * @package App\Components\Finance\Services
 */
class PaymentsService implements PaymentsServiceInterface
{
    /**
     * Returns payment by its id.
     *
     * @param int $paymentId Payment id.
     *
     * @return \App\Components\Finance\Models\Payment
     */
    public function getPayment(int $paymentId): Payment
    {
        return Payment::findOrFail($paymentId);
    }

    /**
     * Allows to find payments relevant for given locations.
     *
     * @param array $locationIds Array of location ids.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findPaymentsByLocations(array $locationIds): Builder
    {
        return Payment::query()
            ->select('payments.*')
            ->leftJoin('transactions AS t', 'payments.transaction_id', '=', 't.id')
            ->whereIn('t.accounting_organization_id', function (QueryBuilder $builder) use ($locationIds) {
                return $builder->select('accounting_organization_id')
                    ->from('accounting_organization_locations')
                    ->whereIn('location_id', $locationIds);
            });
    }

    /**
     * Creates direct deposit payment.
     *
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \Throwable
     */
    public function createDirectDepositPayment(CreatePaymentData $paymentData): Payment
    {
        return $this->createPayment($paymentData, PaymentTypes::DIRECT_DEPOSIT);
    }

    /**
     * Creates credit note payment.
     *
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \Throwable
     */
    public function createCreditNotePayment(CreatePaymentData $paymentData): Payment
    {
        return $this->createPayment($paymentData, PaymentTypes::CREDIT_NOTE);
    }

    /**
     * Creates credit card payment via Pinpayment by default. Used capture = false mode.
     *
     * @see https://pinpayments.com/developers/api-reference/charges#post-charges for details.
     *
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \Throwable
     */
    public function createCreditCardPayment(CreatePaymentData $paymentData): Payment
    {
        $paymentProcessor = app()->make(CreditCardPaymentProcessor::class);
        $purchasedData    = $paymentProcessor->process($paymentData)
                                ->getData()['response'];

        $createdAt = new Carbon($purchasedData['created_at']);

        // paidAt not null fill it with $createdAt value
        $paymentData->setPaidAt($createdAt);

        $captureCallback = function (Payment $payment) use ($paymentData, $purchasedData, $paymentProcessor) {
            $capturedData = $paymentProcessor
                                ->capture($paymentData, $purchasedData['token'])
                                ->getData()['response'];

            $settledAt = new Carbon($capturedData['captured_at']);
            $createdAt = new Carbon($purchasedData['created_at']);

            $creditCardTransaction = new CreditCardTransaction([
                'payment_id'              => $payment->id,
                'amount'                  => $payment->amount,
                'external_transaction_id' => $capturedData['token'],
                'settled_at'              => $settledAt,
                'created_at'              => $createdAt,
            ]);

            $creditCardTransaction->saveOrFail();
            $payment->update(['paid_at' => $settledAt]);
        };

        $payment = $this->createPayment($paymentData, PaymentTypes::CREDIT_CARD, $captureCallback);

        $creditCard = $paymentProcessor->getCreditCard();
        if (!empty($creditCard->getEmail())) {
            $invoice        = $paymentProcessor->getInvoice();
            $recipientEmail = $creditCard->getEmail();

            $receipt = new PaymentReceipt([
                'jobId'                 => $invoice->job_id,
                'paidAt'                => $payment->paid_at,
                'externalTransactionId' => $payment->creditCardTransaction->external_transaction_id,
                'amount'                => $payment->amount,
            ]);
            event(new CreditCardPaymentProcessedEvent($recipientEmail, $receipt));
        }

        return $payment;
    }

    /**
     * @param CreatePaymentData $paymentData
     * @param string            $type
     * @param callable          $afterCreate
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \Throwable
     */
    private function createPayment(CreatePaymentData $paymentData, string $type, callable $afterCreate = null): Payment
    {
        if (!in_array($type, PaymentTypes::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid type %s specified, allowed values are: %s',
                $type,
                implode(',', PaymentTypes::values())
            ));
        }

        $payment = $this->createPaymentModel($paymentData, $type);

        DB::transaction(function () use ($payment, $paymentData, $afterCreate) {
            $this->processPayment($payment, $paymentData);
            if (null !== $afterCreate) {
                $afterCreate($payment);
            }
        });

        return $payment;
    }

    /**
     * Creates new (not saved) payment model.
     *
     * @param CreatePaymentData $paymentData Payment data.
     * @param string            $type        Payment type.
     *
     * @return \App\Components\Finance\Models\Payment
     */
    private function createPaymentModel(CreatePaymentData $paymentData, string $type): Payment
    {
        $payment = new Payment([
            'type'      => $type,
            'amount'    => $paymentData->getAmount(),
            'paid_at'   => $paymentData->getPaidAt(),
            'reference' => $paymentData->getReference(),
        ]);

        $userId = $paymentData->getUserId();
        if (null !== $userId) {
            $payment->user_id = $userId;
        }

        return $payment;
    }

    /**
     * Process the payment (creates transaction and save transaction id to payment).
     *
     * @param Payment           $payment     Payment model that should be processed.
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    private function processPayment(Payment $payment, CreatePaymentData $paymentData): void
    {
        $transaction = FinancialTransaction::make($paymentData->getAccountingOrganizationId());

        foreach ($paymentData->getPayableGLAccountsList() as $accountListItem) {
            $transaction->decrease($accountListItem->getGlAccount(), $accountListItem->getAmount());
        }

        foreach ($paymentData->getReceivableGLAccountsList() as $accountListItem) {
            $glAccount = $accountListItem->getGlAccount();
            if (false === $glAccount->enable_payments_to_account) {
                throw new NotAllowedException('Payments are not enabled for chosen account.');
            }
            $transaction->increase($glAccount, $accountListItem->getAmount());
        }

        $payment->transaction_id = $transaction->commit();

        $payment->saveOrFail();
    }
}
