<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Enums\AccountTypeGroups;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoicePayment;
use App\Components\Reporting\Interfaces\IncomeReportServiceInterface;
use App\Components\Reporting\Models\Filters\IncomeReportFilter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class IncomeReportService
 * Finance: Report - Income by Account Summary.
 *
 * Calculates income based on cash basis. It means that system take into account all paid in full invoices.
 * Also, total income amount = sum of all subtotals by account type - forwarded payment amount.
 * Forwarded payment amount is a funds forwarded to another branch (franchise) from head quarter.
 *
 * @package App\Components\Reporting\Services
 */
class IncomeReportService implements IncomeReportServiceInterface
{
    /** @var GLAccountServiceInterface $glAccountService */
    private $glAccountService;

    /**
     * IncomeReportService constructor.
     *
     * @param \App\Components\Finance\Interfaces\GLAccountServiceInterface $GLAccountService
     */
    public function __construct(GLAccountServiceInterface $GLAccountService)
    {
        $this->glAccountService = $GLAccountService;
    }

    /**
     * @inheritDoc
     */
    public function getIncomeReportData(IncomeReportFilter $filter): array
    {
        $paidInFullInvoices = $this->getAllPaidInFullInvoices($filter);

        $incomeData = $this->getIncomeData($paidInFullInvoices);

        $forwardedAmount = $this->getForwardedSum($paidInFullInvoices);

        return $incomeData->isEmpty()
            ? []
            : $this->buildReport($incomeData, $forwardedAmount);
    }

    /**
     * Returns paid in full invoices identifiers.
     *
     * @param \App\Components\Reporting\Models\Filters\IncomeReportFilter $filter
     *
     * @return \Illuminate\Support\Collection Invoices identifiers
     */
    private function getAllPaidInFullInvoices(IncomeReportFilter $filter): Collection
    {
        $revenueAccountsIds = $this->glAccountService->getGLAccountsByGroupName(
            AccountTypeGroups::REVENUE,
            $filter->getGLAccountId()
        )
            ->pluck('id')
            ->toArray();

        $invoiceItemsTotalQuery = InvoiceItem::withTotalAmountExcludeTax('items_total')
            ->selectRaw('invoice_items.invoice_id')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->whereIn('gl_account_id', $revenueAccountsIds)
            ->groupBy('invoice_items.invoice_id');

        $invoiceItemsFilteredQuery = $filter->apply($invoiceItemsTotalQuery);

        $invoicePaymentTotalQuery = InvoicePayment::query()
            ->selectRaw('invoice_payment.invoice_id, SUM(invoice_payment.amount) AS income, invoice_payment.is_fp')
            ->join('invoices', 'invoice_payment.invoice_id', '=', 'invoices.id')
            ->groupBy('invoice_payment.invoice_id', 'invoice_payment.is_fp');

        $invoicePaymentFilteredQuery = $filter->apply($invoicePaymentTotalQuery);

        $from = sprintf(
            '(%s) AS t1, (%s) AS t2',
            $invoiceItemsFilteredQuery->toSql(),
            $invoicePaymentFilteredQuery->toSql()
        );

        $bindings = [$invoiceItemsFilteredQuery->getBindings(), $invoicePaymentFilteredQuery->getBindings()];

        $resultQuery = DB::query()
            ->select(['t1.invoice_id', 't2.is_fp'])
            ->fromRaw($from, $bindings)
            ->whereRaw('t1.invoice_id = t2.invoice_id AND t2.income >= t1.items_total');

        return $resultQuery->get();
    }

    /**
     * Returns forwarded invoices amount that will be excluded from income.
     *
     * @param \Illuminate\Support\Collection $invoices
     *
     * @return float|null Invoices identifiers
     */
    private function getForwardedSum(Collection $invoices): ?float
    {
        $forwardedInvoices = $invoices->filter(function ($invoice) {
            return $invoice->is_fp === true;
        });

        /** @var Collection $forwardedPayments */
        $forwardedPayments = ForwardedPaymentInvoice::query()
            ->whereIn('invoice_id', $forwardedInvoices->pluck('invoice_id'))
            ->get();

        return $forwardedPayments->sum('amount');
    }

    /**
     * Returns data by specified invoices identifiers. Calculates total income ignoring is_fp flag.
     *
     * @param \Illuminate\Support\Collection $invoices
     *
     * @return \Illuminate\Support\Collection
     */
    private function getIncomeData(Collection $invoices): Collection
    {
        $invoicesIds = $invoices->pluck('invoice_id')
            ->toArray();

        $query = InvoiceItem::withTotalAmountExcludeTax('amount_ex_tax')
            ->selectRaw('at.name AS account_type_name, gl.name AS account_name')
            ->join('gl_accounts AS gl', 'gl.id', '=', 'invoice_items.gl_account_id')
            ->join('account_types AS at', 'at.id', '=', 'gl.account_type_id')
            ->whereIn('invoice_items.invoice_id', $invoicesIds)
            ->groupBy('account_type_name', 'account_name');

        return $query->get();
    }

    /**
     * Builds report based on income data. Groups data by accountTypes and calculates total amount.
     * Excludes forwarded amount from total amount.
     *
     * @param \Illuminate\Support\Collection $incomeData
     * @param float|null                     $forwardedAmount
     *
     * @return array
     */
    private function buildReport(Collection $incomeData, ?float $forwardedAmount): array
    {
        $totalAmount = 0;

        $dataWithSubtotals = $this->calculateSubtotals($incomeData);

        $report = [];
        foreach ($dataWithSubtotals as $glAccountType => $items) {
            $data = [
                'name'     => $glAccountType,
                'accounts' => [
                    'subtotal_amount' => $items['subtotal_amount'],
                ],
            ];

            $totalAmount += $items['subtotal_amount'];
            unset($items['subtotal_amount']);

            foreach ($items as $key => $account) {
                $data['accounts']['items'][] = [
                    'name'          => $key,
                    'amount_ex_tax' => $account['amount_ex_tax'],
                ];
            }
            $report['account_types'][] = $data;
        }

        $report['total_amount']           = $totalAmount;
        $report['total_forwarded_amount'] = 0;

        if (null !== $forwardedAmount) {
            $report['total_amount']           = $totalAmount - $forwardedAmount;
            $report['total_forwarded_amount'] = $forwardedAmount;
        }

        return $report;
    }

    /**
     * Calculates subtotals amount for each account.
     *
     * @param \Illuminate\Support\Collection $incomeData
     *
     * @return array
     */
    private function calculateSubtotals(Collection $incomeData): array
    {
        $result = [];
        foreach ($incomeData as $data) {
            $glAccountType = $data['account_type_name'];
            $glAccountName = $data['account_name'];

            if (!isset($result[$glAccountType])) {
                $result[$glAccountType]['subtotal_amount'] = 0;
            }

            $result[$glAccountType][$glAccountName]['amount_ex_tax'] = $data['amount_ex_tax'];
            $result[$glAccountType]['subtotal_amount']               += $data['amount_ex_tax'];
        }

        return $result;
    }
}
