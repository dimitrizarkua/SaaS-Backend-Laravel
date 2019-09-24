<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Reporting\Interfaces\ReportingPaymentsServiceInterface;
use App\Components\Reporting\Models\VO\InvoicePaymentsReportFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * Class ReportingPaymentsService
 *
 * @package App\Components\Reporting\Services
 */
class ReportingPaymentsService implements ReportingPaymentsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getInvoicePaymentsReportBuilder(InvoicePaymentsReportFilter $filter): Builder
    {
        $commonInvoicesQuery = $this->getCommonInvoicesQuery($filter);

        $query = Invoice::query()
            ->joinSub($commonInvoicesQuery, 'common_invoices', function (JoinClause $join) {
                $join->on('invoices.id', '=', 'common_invoices.id');
            })
            ->select('invoices.*')
            ->with([
                'job',
                'location',
                'recipientContact',
                'payments',
                'items.taxRate',
            ])
            ->orderByDesc('id');

        return $filter->apply($query);
    }

    /**
     * Returns query which retrieve base-filtered invoices ids.
     *
     * @param InvoicePaymentsReportFilter $filter
     *
     * @return Builder
     */
    private function getCommonInvoicesQuery(InvoicePaymentsReportFilter $filter): Builder
    {
        return Invoice::query()
            ->select('id')
            ->whereHas('statuses', function (Builder $query) {
                $query->where('status', FinancialEntityStatuses::APPROVED);
            })
            ->where('location_id', $filter->getLocationId())
            ->whereDate('date', '>=', $filter->getDateFrom())
            ->whereDate('date', '<=', $filter->getDateTo());
    }
}
