<?php

namespace App\Components\Finance\Services;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Finance\Domains\FinancialTransaction;
use App\Components\Finance\Events\AddApproveRequestsToInvoice;
use App\Components\Finance\Events\InvoiceApproved;
use App\Components\Finance\Events\InvoiceCreated;
use App\Components\Finance\Events\InvoiceDeleted;
use App\Components\Finance\Events\InvoicePaymentCreated;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Interfaces\PaymentsServiceInterface;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceApproveRequest;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\CreateFinancialEntityData;
use App\Components\Finance\Models\VO\CreateInvoicePaymentsData;
use App\Components\Finance\Models\VO\CreatePaymentData;
use App\Components\Finance\Models\VO\ReceivePaymentData;
use App\Components\Finance\ViewData\InvoicePrintVersion;
use App\Helpers\Decimal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Class InvoicesService
 *
 * @method FinancialEntity|Invoice getEntity(int $entityId)
 * @package App\Components\Finance\Services
 */
class InvoicesService extends FinancialEntityService
{
    protected $templateName  = 'finance.invoices.print';
    protected $viewDataClass = InvoicePrintVersion::class;

    /**
     * @var \App\Components\Finance\Interfaces\PaymentsServiceInterface
     */
    private $paymentService;

    /**
     * InvoicesService constructor.
     *
     * @param DocumentsServiceInterface               $documentService
     * @param AccountingOrganizationsServiceInterface $accountingOrganizationsService
     * @param PaymentsServiceInterface                $paymentService
     */
    public function __construct(
        DocumentsServiceInterface $documentService,
        AccountingOrganizationsServiceInterface $accountingOrganizationsService,
        PaymentsServiceInterface $paymentService
    ) {
        parent::__construct($documentService, $accountingOrganizationsService);
        $this->paymentService = $paymentService;
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    public function payWithDirectDepositPayment(CreateInvoicePaymentsData $data): Payment
    {
        return $this->pay($data, function (CreatePaymentData $paymentData) {
            return $this->paymentService->createDirectDepositPayment($paymentData);
        });
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     */
    public function payWithCreditNote(CreateInvoicePaymentsData $data): Payment
    {
        return $this->pay($data, function (CreatePaymentData $paymentData) {
            return $this->paymentService->createCreditNotePayment($paymentData);
        });
    }

    /**
     * Pays the invoice with credit card.
     *
     * @param \App\Components\Finance\Models\VO\CreateInvoicePaymentsData $data
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \Throwable
     */
    public function payWithCreditCard(CreateInvoicePaymentsData $data): Payment
    {
        $payment = $this->pay($data, function (CreatePaymentData $paymentData) {
            return $this->paymentService->createCreditCardPayment($paymentData);
        });

        return $payment;
    }

    /**
     * Pay the invoice with given payment.
     *
     * @param CreateInvoicePaymentsData $data
     * @param callable                  $createPayment
     *
     * @return Payment
     * @throws \Throwable
     */
    private function pay(CreateInvoicePaymentsData $data, callable $createPayment): Payment
    {
        $paymentAmount  = $data->payment_data->amount - $data->payment_data->tax;
        $invoicesAmount = $data->getItemsAmount();

        if (false === Decimal::areEquals($paymentAmount, $invoicesAmount)) {
            throw new NotAllowedException('Sum of amount of items should be equals to payment amount.');
        }

        $payment = DB::transaction(function () use ($data, $createPayment) {
            $payment = $createPayment($data->getPaymentData());

            foreach ($data->getInvoicesList() as $invoiceItem) {
                $invoice = $this->getEntity($invoiceItem->invoice_id);

                $invoice->transactions()->attach($payment->transaction_id);
                $invoice->payments()->attach($payment, [
                    'amount' => $invoiceItem->amount,
                    'is_fp'  => $invoiceItem->is_fp,
                ]);
            }

            return $payment;
        });

        foreach ($data->getInvoicesList() as $invoiceItem) {
            $invoice = $this->getEntity($invoiceItem->invoice_id);
            event(new InvoicePaymentCreated($invoice));
        }

        return $payment;
    }

    /**
     * @inheritDoc
     *
     * @param FinancialEntity|Invoice $entity
     *
     * @throws NotAllowedException
     */
    protected function afterApprove(FinancialEntity $entity): void
    {
        $accountingOrganization = $entity->accountingOrganization;

        if (null === $accountingOrganization->receivableAccount) {
            throw new NotAllowedException(
                'There is no receivable account specified for given accounting organization'
            );
        }

        if (null === $accountingOrganization->taxPayableAccount) {
            throw new NotAllowedException(
                'There is no tax payable account specified for given accounting organization'
            );
        }

        $transaction = FinancialTransaction::make($entity->accounting_organization_id);
        $transaction->increase($accountingOrganization->receivableAccount, $entity->getTotalAmount());

        $totalTaxAmount = 0;
        $byGlAccount    = [];

        foreach ($entity->items as $item) {
            if (isset($byGlAccount[$item->glAccount->id])) {
                $byGlAccount[$item->glAccount->id]['subTotal']  += $item->getSubTotal();
            } else {
                $byGlAccount[$item->glAccount->id]['subTotal']  = $item->getSubTotal();
                $byGlAccount[$item->glAccount->id]['glAccount'] = $item->glAccount;
            }
            $totalTaxAmount += $item->getItemTax();
        }

        foreach ($byGlAccount as $gl) {
            $transaction->increase($gl['glAccount'], $gl['subTotal']);
        }

        if (Decimal::gt($totalTaxAmount, 0)) {
            $transaction->increase($accountingOrganization->taxPayableAccount, $totalTaxAmount);
        }

        $transaction->commit();
    }

    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return Invoice::class;
    }

    /**
     * @inheritDoc
     */
    protected function getApproveRequestClass(): string
    {
        return InvoiceApproveRequest::class;
    }

    /**
     * @inheritDoc
     */
    protected function getForeignKeyName(): string
    {
        return 'invoice';
    }

    /**
     * @inheritDoc
     *
     * @param FinancialEntity|Invoice $entity
     *
     * @inheritDoc
     */
    protected function isUserHasCorrectLimit(FinancialEntity $entity, User $user): bool
    {
        return Decimal::gte($user->invoice_approve_limit, $entity->getTotalAmount());
    }

    /**
     * @inheritDoc
     */
    protected function getEventsMap(): array
    {
        return [
            self::EVENT_NAME_CREATED                 => InvoiceCreated::class,
            self::EVENT_NAME_APPROVED                => InvoiceApproved::class,
            self::EVENT_NAME_DELETED                 => InvoiceDeleted::class,
            self::EVENT_NAME_APPROVE_REQUEST_CREATED => AddApproveRequestsToInvoice::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getItemsClassName(): string
    {
        return InvoiceItem::class;
    }

    /**
     * Checks whether is data eligible.
     *
     * @param CreateFinancialEntityData $data Invoice data.
     *
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     */
    protected function checkCreationData(CreateFinancialEntityData $data): void
    {
        parent::checkCreationData($data);
        $accountingOrganization = $this->getAccountingOrganization($data->getLocationId());

        if (null === $accountingOrganization->paymentDetailsAccount) {
            throw new NotAllowedException('Accounting organization must has payment details account');
        }

        if (null === $accountingOrganization->contact->company) {
            throw new NotAllowedException('Contact linked with accounting organization must has a company profile');
        }
    }

    /**
     * Receives payment from one GL account to another.
     *
     * @param ReceivePaymentData $data Receive Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     * @throws \Throwable
     */
    public function receiveInvoicePayment(ReceivePaymentData $data): Payment
    {
        $accountingOrganization = $this->accountingOrganizationsService
            ->findActiveAccountOrganizationByLocation($data->location_id);

        if (null === $accountingOrganization) {
            throw new InvalidArgumentException('Accounting organization has not been found for specified location.');
        }

        $payment = $this->payWithDirectDepositPayment($data->getCreatePaymentData($accountingOrganization));

        return $payment;
    }
}
