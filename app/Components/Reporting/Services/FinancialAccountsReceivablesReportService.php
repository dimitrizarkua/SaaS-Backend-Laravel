<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Reporting\Models\Filters\FinancialReportFilter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * Class FinancialAccountsReceivablesReportService
 *
 * @package App\Components\Reporting\Services
 */
class FinancialAccountsReceivablesReportService extends FinancialReportService
{
    /**
     * {@inheritdoc}
     * @throws \JsonMapper_Exception
     */
    protected function getData(FinancialReportFilter $filter): array
    {
        $chartInvoices = $this->getInvoices($filter);
        $chartInvoices->each(function (Invoice $invoice) {
            $invoice->total_receivables = $invoice->getTotalAmount() - $invoice->getTotalPaid();
        });

        $previousPeriodFilter            = new FinancialReportFilter($filter->toArray());
        $previousPeriodFilter->date_from = null;
        $previousPeriodFilter->date_to   = clone $filter->date_from;
        $previousPeriodInvoices          = $this->getInvoices($previousPeriodFilter);
        $previousPeriodInvoices->each(function (Invoice $invoice) {
            $invoice->total_receivables = $invoice->getTotalAmount() - $invoice->getTotalPaid();
        });

        $groupedByContactsAndDateInvoices = $previousPeriodInvoices
            ->groupBy(function (Invoice $invoice) {
                return $invoice->recipient_contact_id;
            })
            ->map(function (Collection $invoices) {
                return $invoices->groupBy(function (Invoice $invoice) {
                    return $invoice->due_at->toDateString();
                });
            });

        $total    = [
            'current'      => 0,
            'more_30_days' => 0,
            'more_60_days' => 0,
            'more_90_days' => 0,
            'total'        => 0,
        ];
        $contacts = [];
        $groupedByContactsAndDateInvoices->each(
            function (
                Collection $invoicesGroupedByContacts,
                $contactId
            ) use (
                $previousPeriodFilter,
                &$contacts,
                &$total
            ) {
                $invoices             = $invoicesGroupedByContacts->first();
                $contacts[$contactId] = [
                    'name'         => $invoices->first()->recipient_name,
                    'current'      => 0,
                    'more_30_days' => 0,
                    'more_60_days' => 0,
                    'more_90_days' => 0,
                    'total'        => 0,
                ];
                $this->calculateReceivablesForPeriods(
                    $invoicesGroupedByContacts,
                    $previousPeriodFilter->date_to,
                    $contacts[$contactId],
                    $total
                );
            }
        );

        $result = $total + [
                'contacts' => $contacts,
                'chart'    => $this->getChart($filter, $chartInvoices->groupBy(function (Invoice $invoice) {
                    return $invoice->due_at->toDateString();
                }), 'total_receivables'),
            ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataWithComparing(array $currentPeriodData, array $previousPeriodData): array
    {
        return array_merge($currentPeriodData, [
            'current_change'          => $this->compare($currentPeriodData, $previousPeriodData, 'current'),
            'more_30_days_change'     => $this->compare($currentPeriodData, $previousPeriodData, 'more_30_days'),
            'more_60_days_change'     => $this->compare($currentPeriodData, $previousPeriodData, 'more_60_days'),
            'more_90_days_change'     => $this->compare($currentPeriodData, $previousPeriodData, 'more_90_days'),
            'total_change'            => $this->compare($currentPeriodData, $previousPeriodData, 'total'),
            'previous_interval_chart' => $previousPeriodData['chart'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getInvoices(FinancialReportFilter $filter, bool $byJob = false): Collection
    {
        $invoicesIds = Invoice::query()
            ->select(['id'])
            ->when($byJob, function (Builder $query) use ($filter) {
                return $query->whereIn('job_id', $filter->job_ids);
            }, function (Builder $query) use ($filter) {
                return $query->where('location_id', $filter->location_id);
            })
            ->when($filter->date_from, function (Builder $query) use ($filter) {
                return $query->whereDate('due_at', '>=', $filter->date_from);
            })
            ->when($filter->date_to, function (Builder $query) use ($filter) {
                return $query->whereDate('due_at', '<=', $filter->date_to);
            })
            ->orderByDesc('id')
            ->get();

        $preparedInvoicesIds = $invoicesIds
            ->when(
                isset($filter->tag_ids),
                function (EloquentCollection $invoicesIds) use ($filter) {
                    return $invoicesIds->load('tags')
                        ->filter(function (Invoice $invoice) use ($filter) {
                            return $invoice->tags->whereIn('id', $filter->tag_ids);
                        });
                }
            )
            ->when(
                isset($filter->gl_account_id),
                function (EloquentCollection $invoicesIds) use ($filter) {
                    return $invoicesIds->load('items')
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
            ['due_at', 'recipient_contact_id']
        );
    }

    /**
     * @param \Illuminate\Support\Collection $invoices
     * @param \Carbon\Carbon                 $dateTo
     * @param array                          $contact
     * @param array                          $total
     */
    private function calculateReceivablesForPeriods(
        Collection $invoices,
        Carbon $dateTo,
        array &$contact,
        array &$total
    ): void {
        $invoices->each(
            function (Collection $invoices, $date) use ($dateTo, &$contact, &$total) {
                $date = Carbon::make($date);
                if ($date->lte($dateTo)) {
                    $dailyAmount = $invoices->sum('total_receivables');
                    if (Carbon::make($date)->diffInDays($dateTo) <= 30) {
                        $contact['current'] += $dailyAmount;
                        $total['current']   += $dailyAmount;
                    } elseif (Carbon::make($date)->diffInDays($dateTo) <= 60) {
                        $contact['more_30_days'] += $dailyAmount;
                        $total['more_30_days']   += $dailyAmount;
                    } elseif (Carbon::make($date)->diffInDays($dateTo) <= 90) {
                        $contact['more_60_days'] += $dailyAmount;
                        $total['more_60_days']   += $dailyAmount;
                    } else {
                        $contact['more_90_days'] += $dailyAmount;
                        $total['more_90_days']   += $dailyAmount;
                    }
                    $contact['total'] += $dailyAmount;
                    $total['total']   += $dailyAmount;
                }
            }
        );
    }
}
