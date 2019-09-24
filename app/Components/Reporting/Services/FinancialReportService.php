<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Reporting\Interfaces\FinancialReportServiceInterface;
use App\Components\Reporting\Models\Chartable;
use App\Components\Reporting\Models\Filters\FinancialReportFilter;
use App\Components\Reporting\Models\Filters\FinancialReportFilterData;
use App\Components\Reporting\Models\UsageCosts;
use App\Helpers\Decimal;
use App\Models\HasLatestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class FinancialReportService
 *
 * @package App\Components\Reporting\Services
 */
abstract class FinancialReportService implements FinancialReportServiceInterface
{
    use Chartable, UsageCosts, HasLatestStatus;

    /**
     * Returns list of values for chosen date period.
     *
     * @param \App\Components\Reporting\Models\Filters\FinancialReportFilter $filter Options to filter.
     *
     * @return array
     */
    abstract protected function getData(FinancialReportFilter $filter): array;

    /**
     * Returns list of values for chosen date period and comparing with previous period.
     *
     * @param array $currentPeriodData  Prepared data for current period for comparing.
     * @param array $previousPeriodData Prepared data for previous period for comparing.
     *
     * @return array
     */
    abstract protected function getDataWithComparing(array $currentPeriodData, array $previousPeriodData): array;

    /**
     * {@inheritdoc}
     */
    public function getReport(FinancialReportFilterData $filterData): array
    {
        $filterData->updateJobIds();

        $currentPeriodFilter = new FinancialReportFilter([
            'location_id'   => $filterData->getLocationId(),
            'tag_ids'       => $filterData->getTagIds(),
            'date_from'     => $filterData->getCurrentDateFrom(),
            'date_to'       => $filterData->getCurrentDateTo(),
            'gl_account_id' => $filterData->gl_account_id,
            'job_ids'       => $filterData->getCurrentPeriodJobIds(),
        ]);
        $currentPeriodData   = $this->getData($currentPeriodFilter);

        $previousPeriodFilter = new FinancialReportFilter([
            'location_id'   => $filterData->getLocationId(),
            'tag_ids'       => $filterData->getTagIds(),
            'date_from'     => $filterData->getPreviousDateFrom(),
            'date_to'       => $filterData->getPreviousDateTo(),
            'gl_account_id' => $filterData->gl_account_id,
            'job_ids'       => $filterData->getPreviousPeriodJobIds(),
        ]);
        $previousPeriodData   = $this->getData($previousPeriodFilter);

        $result = $this->getDataWithComparing($currentPeriodData, $previousPeriodData);

        return $result;
    }

    /**
     * @param array  $currentPeriodData  Prepared data for current period for comparing.
     * @param array  $previousPeriodData Prepared data for previous period for comparing.
     * @param string $key                Key of element for comparing.
     *
     * @return int
     */
    protected function compare(array $currentPeriodData, array $previousPeriodData, string $key): int
    {
        if (!isset($previousPeriodData[$key]) || Decimal::isZero($previousPeriodData[$key])) {
            return 0;
        }

        return ($currentPeriodData[$key] / $previousPeriodData[$key] - 1) * 100;
    }

    /**
     * Returns list of invoices for listed jobs.
     *
     * @param FinancialReportFilter $filter Options to filter.
     * @param bool                  $byJob  Search entities assigned to jobs in interval.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getInvoices(FinancialReportFilter $filter, bool $byJob = false): Collection
    {
        $invoicesIds = Invoice::query()
            ->select(['id'])
            ->when($byJob, function (Builder $query) use ($filter) {
                return $query->whereIn('job_id', $filter->job_ids);
            }, function (Builder $query) use ($filter) {
                return $query->whereDate('date', '>=', $filter->date_from)
                    ->whereDate('date', '<=', $filter->date_to)
                    ->where('location_id', $filter->location_id);
            })
            ->orderByDesc('id')
            ->get();

        $preparedInvoicesIds = $invoicesIds
            ->when(
                !$byJob && isset($filter->tag_ids),
                function (\Illuminate\Database\Eloquent\Collection $preparedInvoicesIds) use ($filter) {
                    return $preparedInvoicesIds->load('tags')
                        ->filter(function (Invoice $invoice) use ($filter) {
                            return $invoice->tags->whereIn('id', $filter->tag_ids);
                        });
                }
            )
            ->when(
                isset($filter->gl_account_id),
                function (\Illuminate\Database\Eloquent\Collection $preparedInvoicesIds) use ($filter) {
                    return $preparedInvoicesIds->load('items')
                        ->filter(function (Invoice $invoice) use ($filter) {
                            return $invoice->items->where('gl_account_id', $filter->gl_account_id);
                        });
                }
            )
            ->pluck('id')
            ->toArray();

        return Invoice::getCollectionByStatuses(
            $preparedInvoicesIds,
            [FinancialEntityStatuses::APPROVED],
            ['items.taxRate', 'payments'],
            ['date', 'due_at', 'recipient_contact_id']
        );
    }

    /**
     * Returns list of credit notes for listed jobs.
     *
     * @param FinancialReportFilter $filter Options to filter.
     * @param bool                  $byJob  Search entities assigned to jobs in interval.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getCreditNotes(FinancialReportFilter $filter, bool $byJob = false): Collection
    {
        $creditNotesId = CreditNote::query()
            ->select(['id'])
            ->when($byJob, function (Builder $query) use ($filter) {
                return $query->whereIn('job_id', $filter->job_ids);
            }, function (Builder $query) use ($filter) {
                return $query->whereDate('date', '>=', $filter->date_from)
                    ->whereDate('date', '<=', $filter->date_to)
                    ->where('location_id', $filter->location_id);
            })
            ->orderByDesc('id')
            ->get();

        $preparedCreditNotesIds = $creditNotesId
            ->when(
                !$byJob && isset($filter->tag_ids),
                function (\Illuminate\Database\Eloquent\Collection $preparedCreditNotesIds) use ($filter) {
                    return $preparedCreditNotesIds->load('tags')
                        ->filter(function (CreditNote $creditNote) use ($filter) {
                            return $creditNote->tags->whereIn('id', $filter->tag_ids);
                        });
                }
            )
            ->when(
                isset($filter->gl_account_id),
                function (\Illuminate\Database\Eloquent\Collection $preparedCreditNotesIds) use ($filter) {
                    return $preparedCreditNotesIds->load('items')
                        ->filter(function (CreditNote $creditNote) use ($filter) {
                            return $creditNote->items->where('gl_account_id', $filter->gl_account_id);
                        });
                }
            )
            ->pluck('id')
            ->toArray();

        return CreditNote::getCollectionByStatuses(
            $preparedCreditNotesIds,
            [FinancialEntityStatuses::APPROVED],
            ['items.taxRate'],
            ['date']
        );
    }

    /**
     * Returns list of purchase orders for listed jobs.
     *
     * @param FinancialReportFilter $filter Options to filter.
     * @param bool                  $byJob  Search entities assigned to jobs in interval.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getPurchaseOrders(FinancialReportFilter $filter, bool $byJob = false): Collection
    {
        $purchaseOrdersIds = PurchaseOrder::query()
            ->select(['id'])
            ->when($byJob, function (Builder $query) use ($filter) {
                return $query->whereIn('job_id', $filter->job_ids);
            }, function (Builder $query) use ($filter) {
                return $query->whereDate('date', '>=', $filter->date_from)
                    ->whereDate('date', '<=', $filter->date_to)
                    ->where('location_id', $filter->location_id);
            })
            ->orderByDesc('id')
            ->get();

        $preparedPurchaseOrdersIds = $purchaseOrdersIds
            ->when(
                !$byJob && isset($filter->tag_ids),
                function (\Illuminate\Database\Eloquent\Collection $preparedPurchaseOrdersIds) use ($filter) {
                    return $preparedPurchaseOrdersIds->load('tags')
                        ->filter(function (PurchaseOrder $purchaseOrder) use ($filter) {
                            return $purchaseOrder->tags->whereIn('id', $filter->tag_ids);
                        });
                }
            )
            ->when(
                isset($filter->gl_account_id),
                function (\Illuminate\Database\Eloquent\Collection $preparedPurchaseOrdersIds) use ($filter) {
                    return $preparedPurchaseOrdersIds->load('items')
                        ->filter(function (PurchaseOrder $purchaseOrder) use ($filter) {
                            return $purchaseOrder->items->where('gl_account_id', $filter->gl_account_id);
                        });
                }
            )
            ->pluck('id')
            ->toArray();

        return PurchaseOrder::getCollectionByStatuses(
            $preparedPurchaseOrdersIds,
            [FinancialEntityStatuses::APPROVED],
            ['items.taxRate'],
            ['date']
        );
    }
}
