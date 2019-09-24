<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\VO\GLAccountTransactionFilter;
use App\Components\Locations\Models\Location;
use App\Components\Reporting\Models\Filters\FinancialReportFilter;
use App\Components\Tags\Models\Tag;
use Illuminate\Support\Collection;

/**
 * Class FinancialRevenueReportService
 *
 * @package App\Components\Reporting\Services
 */
class FinancialRevenueReportService extends FinancialReportService
{
    /** @var GLAccountServiceInterface $glAccountService */
    private $glAccountService;

    /**
     * FinancialRevenueReportService constructor.
     *
     * @param \App\Components\Finance\Interfaces\GLAccountServiceInterface $GLAccountService
     */
    public function __construct(GLAccountServiceInterface $GLAccountService)
    {
        $this->glAccountService = $GLAccountService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \JsonMapper_Exception
     */
    protected function getData(FinancialReportFilter $filter): array
    {
        $invoices = $this->getInvoices($filter);
        $invoices->each(function (Invoice $invoice) {
            $invoice->total_amount      = $invoice->getTotalAmount();
            $invoice->total_amount_paid = $invoice->getTotalPaid();
        });
        $invoicesTotalAmountPaid = $invoices->sum('total_amount_paid');
        $invoicesTotalAmount     = $invoices->sum('total_amount');

        $creditNotes = $this->getCreditNotes($filter);
        $creditNotes->each(function (CreditNote $creditNote) {
             $creditNote->total_amount = $creditNote->getTotalAmount();
        });
        $creditNotesTotalAmount = $creditNotes->sum('total_amount');

        $purchaseOrders = $this->getPurchaseOrders($filter);
        $purchaseOrders->each(function (PurchaseOrder $purchaseOrder) {
             $purchaseOrder->total_amount = $purchaseOrder->getTotalAmount();
        });
        $purchaseOrdersTotalAmount = $purchaseOrders->sum('total_amount');

        $totalCharged            = $invoicesTotalAmount - $creditNotesTotalAmount;
        $totalCost               = $this->getLaboursTotalCost($filter->job_ids)
            + $this->getMaterialTotalCost($filter->job_ids)
            + $this->getEquipmentTotalCost($filter->job_ids)
            + $purchaseOrdersTotalAmount
            + $this->getReimbursementTotalCost($filter->job_ids)
            + $this->getLahaCompensationTotalCost($filter->job_ids);
        $assessmentReportsAmount = $this->getAssessmentReportsAmount($filter->job_ids);
        $jobCount                = count($filter->job_ids);

        $result = [
            'invoices_paid'      => $invoicesTotalAmountPaid,
            'invoices_written'   => $invoicesTotalAmount,
            'avg_job_cost'       => $jobCount > 0 ? $totalCost / $jobCount : 0,
            'avg_over_job_cost'  => $jobCount > 0 ? $assessmentReportsAmount / $jobCount : 0,
            'credit_notes'       => $creditNotesTotalAmount,
            'total_gross_profit' => $totalCharged
                ? ($totalCharged - $totalCost) / ($totalCharged) * 100
                : 0,
            'tagged_invoices'    => $this->getTaggedInvoices($invoices),
            'revenue_accounts'   => $this->getRevenueAccounts($filter),
            'chart'              => $this->getChart($filter, $invoices->groupBy(function (Invoice $invoice) {
                return $invoice->date->toDateString();
            }), 'total_amount_paid'),
        ];

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataWithComparing(array $currentPeriodData, array $previousPeriodData): array
    {
        foreach ($currentPeriodData['tagged_invoices'] as $name => &$tag) {
            $previousTagPercent = $previousPeriodData['tagged_invoices'][$name]['percent'] ?? 0;
            $tag['change']      = $previousTagPercent
                ? ($tag['percent'] / $previousTagPercent - 1) * 100
                : 0;
        }

        return array_merge($currentPeriodData, [
            'invoices_paid_change'      => $this->compare($currentPeriodData, $previousPeriodData, 'invoices_paid'),
            'invoices_written_change'   => $this->compare($currentPeriodData, $previousPeriodData, 'invoices_written'),
            'avg_job_cost_change'       => $this->compare($currentPeriodData, $previousPeriodData, 'avg_job_cost'),
            'avg_over_job_cost_change'  => $this->compare($currentPeriodData, $previousPeriodData, 'avg_over_job_cost'),
            'credit_notes_change'       => $this->compare($currentPeriodData, $previousPeriodData, 'credit_notes'),
            'total_gross_profit_change' => $this->compare(
                $currentPeriodData,
                $previousPeriodData,
                'total_gross_profit'
            ),
            'previous_interval_chart'   => $previousPeriodData['chart'],
        ]);
    }

    /**
     * Returns list of tags with percentage used compared to all tags for list of invoices.
     *
     * @param Collection $invoices
     *
     * @return array
     */
    private function getTaggedInvoices(Collection $invoices): array
    {
        $tags     = [];
        $untagged = 0;
        $count    = 0;
        $invoices->each(function (Invoice $invoice) use (&$tags, &$count, &$untagged) {
            if ($invoice->tags->count() === 0) {
                $untagged++;
                $count++;
            }
            $invoice->tags->each(function (Tag $tag) use (&$tags, &$count) {
                isset($tags[$tag->name]['count'])
                    ? $tags[$tag->name]['count'] += 1
                    : $tags[$tag->name]['count'] = 1;
                $count++;
            });
        });
        if ($untagged) {
            $tags['untagged']['count'] = $untagged;
        }
        foreach ($tags as $key => &$tag) {
            $tag['name']    = $key;
            $tag['percent'] = $count ? $tag['count'] / $count * 100 : 0;
        }

        return array_values($tags);
    }

    /**
     * Returns list of revenue accounts with amounts for list of invoices
     *
     * @param \App\Components\Reporting\Models\Filters\FinancialReportFilter $filter
     *
     * @return array
     * @throws \JsonMapper_Exception
     */
    private function getRevenueAccounts(FinancialReportFilter $filter): array
    {
        $accountingOrganization = Location::findOrFail($filter->location_id)->accountingOrganizations->last();

        if (null === $accountingOrganization) {
            return [];
        }
        $accounts = GLAccount::query()
            ->selectRaw('gl_accounts.id, gl_accounts.name, gl_accounts.code')
            ->where('accounting_organization_id', $accountingOrganization->id)
            ->leftJoin('account_types', 'gl_accounts.account_type_id', '=', 'account_types.id')
            ->leftJoin('account_type_groups', 'account_types.account_type_group_id', '=', 'account_type_groups.id')
            ->where('account_type_groups.name', AccountTypeGroups::REVENUE)
            ->get();

        $glAccountFilter = new GLAccountTransactionFilter([
            'date_from' => $filter->date_from,
            'date_to'   => $filter->date_to,
        ]);

        return $accounts->each(function (GLAccount $item) use ($glAccountFilter) {
            $item['amount'] = $this->glAccountService->getAccountBalance($item->id, $glAccountFilter);
            unset($item['id']);
        })->toArray();
    }
}
