<?php

namespace App\Components\Finance\DataProviders;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\InvoiceListingDataProviderInterface;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Helpers\Decimal;
use App\Models\Filter;
use App\Models\HasLatestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class InvoiceListingDataProvider
 *
 * @package App\Components\Finance\DataProviders
 */
class InvoiceListingDataProvider implements InvoiceListingDataProviderInterface
{
    use HasLatestStatus;

    /**
     * @inheritDoc
     */
    public function getDraft(Filter $filter): Collection
    {
        return $this->getDraftInvoicesQuery($filter)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getUnpaid(Filter $filter): Collection
    {
        return $this->getApprovedInvoicesQuery($filter)
            ->whereRaw('NOW() <= due_at')
            ->get()
            ->filter(function (Invoice $invoice) {
                return null === $invoice->total_amount_paid
                    || Decimal::gt($invoice->total_amount, $invoice->total_amount_paid);
            });
    }

    /**
     * @inheritDoc
     */
    public function getOverdue(Filter $filter): Collection
    {
        return $this->getApprovedInvoicesQuery($filter)
            ->whereRaw('NOW() > due_at')
            ->get()
            ->filter(function (Invoice $invoice) {
                return false === Decimal::isZero($invoice->getAmountDue());
            });
    }

    /**
     * @inheritDoc
     */
    public function getAll(Filter $filter): Builder
    {
        return $this->getCommonQuery($filter)
            ->orderBy('created_at', 'DESC');
    }

    /**
     * @inheritDoc
     */
    public function getApproved(Filter $filter): Collection
    {
        return $this->getApprovedInvoicesQuery($filter)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    /**
     * Returns approved invoices.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getApprovedInvoicesQuery(Filter $filter): Builder
    {
        return $this->getCommonQuery($filter)
            ->whereIn(
                'invoices.id',
                $this->getEntityIdsWhereLatestStatusIs('invoices', [FinancialEntityStatuses::APPROVED])
            );
    }

    /**
     * Query that returns draft invoices.
     *
     * @param \App\Models\Filter $filter
     *
     * @return Builder
     */
    private function getDraftInvoicesQuery(Filter $filter): Builder
    {
        return $this->getCommonQuery($filter)
            ->whereIn(
                'invoices.id',
                $this->getEntityIdsWhereLatestStatusIs('invoices', [FinancialEntityStatuses::DRAFT])
            );
    }

    /**
     * Returns common for all listing operations.
     *
     * @param Filter $filter Filter instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getCommonQuery(Filter $filter): Builder
    {
        $totalPaidQuery    = InvoicePayment::totalPaidAmountSubQueryString('total_amount_paid');
        $totalAmountQuery  = InvoiceItem::totalAmountIncludeTaxSubQueryString('total_amount');
        $latestStatusQuery = '
            (SELECT status from public.invoice_statuses 
            WHERE invoice_id=invoices.id
            ORDER BY created_at DESC
            LIMIT 1
            ) AS latest_status
        ';

        $query = Invoice::query()
            ->select([
                'invoices.*',
                DB::raw($totalPaidQuery),
                DB::raw($totalAmountQuery),
                DB::raw($latestStatusQuery),
            ])
            ->orderByDesc('id');

        return $filter->apply($query);
    }
}
