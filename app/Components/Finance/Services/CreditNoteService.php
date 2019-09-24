<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Events\AddApproveRequestsToCreditNote;
use App\Components\Finance\Events\CreditNoteApproved;
use App\Components\Finance\Events\CreditNoteCreated;
use App\Components\Finance\Events\CreditNoteDeleted;
use App\Components\Finance\Events\CreditNoteUpdated;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteApproveRequest;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\CreateInvoicePaymentsData;
use App\Components\Finance\Models\VO\CreatePaymentData;
use App\Components\Finance\ViewData\CreditNotesPrintVersion;
use App\Helpers\Decimal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class CreditNoteService
 *
 * @package App\Components\Finance\Services
 *
 * @method FinancialEntity|CreditNote getEntity(int $entityId)
 */
class CreditNoteService extends FinancialEntityService
{
    protected $templateName  = 'finance.creditNotes.print';
    protected $viewDataClass = CreditNotesPrintVersion::class;

    /**
     * @var InvoicesService
     */
    private $invoiceService;

    /**
     * Returns list of users who can approve specific credit note.
     *
     * @param int $creditNoteId
     *
     * @throws \Throwable
     *
     * @return User[]|Collection
     */
    public function getApproversList(int $creditNoteId): Collection
    {
        $creditNote = $this->getEntity($creditNoteId);

        return User::query()
            ->leftJoin('location_user', 'users.id', '=', 'location_user.user_id')
            ->where('credit_note_approval_limit', '>=', $creditNote->getSubTotalAmount())
            ->where('location_user.location_id', $creditNote->location_id)
            ->get();
    }

    /**
     * @param array|\App\Components\Finance\Models\VO\PaymentInvoiceItem[] $paymentItems
     * @param \App\Components\Finance\Models\CreditNote                    $creditNote
     *
     * @return \App\Components\Finance\Models\Payment
     *
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function createPaymentForCreditNote(array $paymentItems, CreditNote $creditNote): Payment
    {
        $totalAmount = $creditNote->getTotalAmount();
        $totalTax    = $creditNote->getTaxesAmount();

        $accountList[] = [
            'glAccount' => $creditNote->accountingOrganization->receivableAccount,
            'amount'    => $totalAmount,
        ];
        if ($totalTax) {
            $accountList[] = [
                'glAccount' => $creditNote->accountingOrganization->taxPayableAccount,
                'amount'    => $totalTax,
            ];
        }
        foreach ($creditNote->items as $item) {
            $accountList[] = [
                'glAccount' => $item->glAccount,
                'amount'    => $item->getSubTotal(),
            ];
        }
        $paymentData = new CreatePaymentData([
            'amount'                   => $totalAmount,
            'tax'                      => $totalTax,
            'paidAt'                   => Carbon::now(),
            'accountingOrganizationId' => $creditNote->accountingOrganization->id,
            'payableGLAccountList'     => $accountList,
        ]);

        $invoicePaymentData = new CreateInvoicePaymentsData([
            'payment_data'  => $paymentData,
            'invoices_list' => $paymentItems,
        ]);

        DB::transaction(function () use ($invoicePaymentData, &$creditNote, &$payment) {
            $payment = $this->getInvoiceService()
                ->payWithCreditNote($invoicePaymentData);
            $creditNote->update(['payment_id' => $payment->id]);
        });

        return $payment;
    }

    /**
     * @return \App\Components\Finance\Services\InvoicesService
     */
    private function getInvoiceService(): InvoicesService
    {
        if (null === $this->invoiceService) {
            $this->invoiceService = app()->make(InvoicesService::class);
        }

        return $this->invoiceService;
    }

    /**
     * @inheritDoc
     */
    protected function getEventsMap(): array
    {
        return [
            self::EVENT_NAME_CREATED                 => CreditNoteCreated::class,
            self::EVENT_NAME_DELETED                 => CreditNoteDeleted::class,
            self::EVENT_NAME_APPROVE_REQUEST_CREATED => AddApproveRequestsToCreditNote::class,
            self::EVENT_NAME_APPROVED                => CreditNoteApproved::class,
            self::EVENT_NAME_UPDATED                 => CreditNoteUpdated::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return CreditNote::class;
    }

    /**
     * @inheritDoc
     */
    protected function getItemsClassName(): string
    {
        return CreditNoteItem::class;
    }

    /**
     * @inheritDoc
     */
    protected function getApproveRequestClass(): string
    {
        return CreditNoteApproveRequest::class;
    }

    /**
     * @inheritDoc
     */
    protected function getForeignKeyName(): string
    {
        return 'credit_note';
    }

    /**
     * @inheritDoc
     *
     * @param FinancialEntity|CreditNote $entity
     */
    protected function isUserHasCorrectLimit(FinancialEntity $entity, User $user): bool
    {
        return Decimal::gte($user->credit_note_approval_limit, $entity->getTotalAmount());
    }
}
