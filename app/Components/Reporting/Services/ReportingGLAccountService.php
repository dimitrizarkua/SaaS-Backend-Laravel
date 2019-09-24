<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface;
use App\Components\Reporting\Models\VO\GlAccountTrialReportFilterData;
use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ReportingGLAccountService
 *
 * @package App\Components\Reporting\Services
 */
class ReportingGLAccountService implements ReportingGLAccountServiceInterface
{
    /** @var array */
    private $reportAccountTypeGroups = [
        AccountTypeGroups::REVENUE,
        AccountTypeGroups::ASSET,
        AccountTypeGroups::LIABILITY,
    ];

    /**
     * {@inheritdoc}
     */
    public function getGlAccountTrialReport(GlAccountTrialReportFilterData $filterData): Collection
    {
        $accountingOrganizationId = $this->getAccountingOrganizationId($filterData->getLocationId());

        $reportDataGlAccounts = $this
            ->getGlAccountsTrialReportBuilder($accountingOrganizationId)
            ->get();

        $reportGlAccountsIds = $reportDataGlAccounts->pluck('gl_account_id')->toArray();

        $reportData = collect();

        $reportDataFull = $this
            ->getAmountsTrialReportBuilder(
                $accountingOrganizationId,
                $reportGlAccountsIds,
                $filterData->getDateTo()
            )
            ->get()
            ->keyBy('gl_account_id');

        $reportDataYtd = $this
            ->getAmountsTrialReportBuilder(
                $accountingOrganizationId,
                $reportGlAccountsIds,
                $filterData->getDateTo(),
                DateHelper::getFinancialYearStart()
            )->get()
            ->keyBy('gl_account_id');

        foreach ($reportDataGlAccounts as $reportDataGlAccount) {
            $glAccountId    = $reportDataGlAccount->gl_account_id;
            $reportItem     = $reportDataFull[$glAccountId] ?? null;
            $reportItemYtd  = $reportDataYtd[$glAccountId] ?? null;
            $reportDataItem = [
                'group_name'        => $reportDataGlAccount->group_name,
                'gl_account_name'   => $reportDataGlAccount->gl_account_name,
                'gl_account_id'     => $reportDataGlAccount->gl_account_id,
                'debit_amount'      => $reportItem->debit_amount ?? 0,
                'credit_amount'     => $reportItem->credit_amount ?? 0,
                'debit_amount_ytd'  => $reportItemYtd->debit_amount ?? 0,
                'credit_amount_ytd' => $reportItemYtd->credit_amount ?? 0,

            ];
            $reportData->push($reportDataItem);
        }

        $total                      = [];
        $total['gl_account_name']   = $total['group_name'] = 'TOTAL';
        $total['debit_amount']      = $reportData->sum('debit_amount');
        $total['credit_amount']     = $reportData->sum('credit_amount');
        $total['debit_amount_ytd']  = $reportData->sum('debit_amount_ytd');
        $total['credit_amount_ytd'] = $reportData->sum('credit_amount_ytd');
        $reportData->push($total);

        return $reportData->groupBy('group_name');
    }

    /**
     * @param int                 $accountingOrganizationId
     * @param array               $reportGlAccountsIds
     * @param \Carbon\Carbon      $dateTo
     * @param \Carbon\Carbon|null $dateFrom
     *
     * @throws \Throwable
     * @return Builder
     */
    private function getAmountsTrialReportBuilder(
        int $accountingOrganizationId,
        array $reportGlAccountsIds,
        Carbon $dateTo,
        Carbon $dateFrom = null
    ): Builder {
        $debitAmount  = 'SUM( CASE WHEN selected_transaction_records.is_debit THEN 
            selected_transaction_records.amount ELSE 0 END )';
        $creditAmount = 'SUM( CASE WHEN NOT selected_transaction_records.is_debit THEN 
            selected_transaction_records.amount ELSE 0 END )';

        $query = DB::query()
            ->select([
                'gl_account_id',
                DB::raw(sprintf('(%s) AS debit_amount', $debitAmount)),
                DB::raw(sprintf('(%s) AS credit_amount', $creditAmount)),
            ])
            ->fromSub(
                DB::query()
                    ->select(['id'])
                    ->from('transactions')
                    ->where('accounting_organization_id', $accountingOrganizationId)
                    ->whereDate('created_at', '<=', $dateTo)
                    ->when($dateFrom, function (Builder $query) use ($dateFrom) {
                        return $query->whereDate('created_at', '>=', $dateFrom);
                    }),
                'transaction_ids'
            )->joinSub(
                DB::query()->select(['amount', 'is_debit', 'gl_account_id', 'transaction_id'])
                    ->from('transaction_records')
                    ->whereIn('gl_account_id', $reportGlAccountsIds),
                'selected_transaction_records',
                'transaction_ids.id',
                '=',
                'selected_transaction_records.transaction_id'
            )->groupBy([
                'gl_account_id',
            ]);

        return $query;
    }

    /**
     * Creates builder returning list of GL accounts and account type groups.
     *
     * @param int $accountingOrganizationId
     *
     * @throws \Throwable
     * @return Builder
     */
    private function getGlAccountsTrialReportBuilder(int $accountingOrganizationId): Builder
    {
        $query = DB::query()
            ->select([
                'gl_accounts.id as gl_account_id',
                'gl_accounts.name as gl_account_name',
                'account_type_groups.name as group_name',
            ])
            ->fromSub(
                DB::query()
                    ->select(['gl_accounts.id', 'gl_accounts.account_type_id', 'gl_accounts.name'])
                    ->from('gl_accounts')
                    ->where('accounting_organization_id', $accountingOrganizationId),
                'gl_accounts'
            )->join(
                'account_types',
                'gl_accounts.account_type_id',
                '=',
                'account_types.id'
            )->rightJoinSub(
                DB::query()->select(['id', 'name'])
                    ->from('account_type_groups')
                    ->whereIn('name', $this->reportAccountTypeGroups),
                'account_type_groups',
                'account_types.account_type_group_id',
                '=',
                'account_type_groups.id'
            );

        return $query;
    }

    /**
     * @param int $locationId
     *
     * @throws \InvalidArgumentException
     * @return int
     */
    private function getAccountingOrganizationId(int $locationId): int
    {
        $accountingOrganizationsService = app()->make(AccountingOrganizationsServiceInterface::class);

        $accountingOrganization = $accountingOrganizationsService->findActiveAccountOrganizationByLocation($locationId);

        if (null === $accountingOrganization) {
            throw new \InvalidArgumentException('Accounting organization has not been found for specified location.');
        }

        return $accountingOrganization->id;
    }
}
