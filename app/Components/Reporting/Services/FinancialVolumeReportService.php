<?php

namespace App\Components\Reporting\Services;

use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Reporting\Models\Filters\FinancialReportFilter;

/**
 * Class FinancialVolumeReportService
 *
 * @package App\Components\Reporting\Services
 */
class FinancialVolumeReportService extends FinancialReportService
{
    /**
     * {@inheritdoc}
     */
    protected function getData(FinancialReportFilter $filter): array
    {
        $invoices = $this->getInvoices($filter, true);
        $invoices->each(function (Invoice $invoice) {
            $invoice->total_amount      = $invoice->getTotalAmount();
            $invoice->total_amount_paid = $invoice->getTotalPaid();
        });
        $invoicesTotalAmountPaid = $invoices->sum('total_amount_paid');
        $invoicesTotalAmount     = $invoices->sum('total_amount');
        $invoicesCount           = $invoices->count();

        $creditNotes = $this->getCreditNotes($filter, true);
        $creditNotes->each(function (CreditNote $creditNote) {
            $creditNote->total_amount = $creditNote->getTotalAmount();
        });
        $creditNotesTotalAmount = $creditNotes->sum('total_amount');
        $creditNotesCount       = $creditNotes->count();

        $purchaseOrders = $this->getPurchaseOrders($filter, true);
        $purchaseOrders->each(function (PurchaseOrder $purchaseOrder) {
            $purchaseOrder->total_amount = $purchaseOrder->getTotalAmount();
        });
        $purchaseOrdersTotalAmount = $purchaseOrders->sum('total_amount');
        $purchaseOrdersCount       = $purchaseOrders->count();

        $totalCharged = $invoicesTotalAmount - $creditNotesTotalAmount;
        $totalCost    = $this->getLaboursTotalCost($filter->job_ids)
            + $this->getMaterialTotalCost($filter->job_ids)
            + $this->getEquipmentTotalCost($filter->job_ids)
            + $purchaseOrdersTotalAmount
            + $this->getReimbursementTotalCost($filter->job_ids)
            + $this->getLahaCompensationTotalCost($filter->job_ids);

        $result = [
            'total_revenue'       => $invoicesTotalAmountPaid,
            'from_jobs'           => count($filter->job_ids),
            'invoices'            => $invoicesCount,
            'purchase_orders'     => $purchaseOrdersCount,
            'credit_notes'        => $creditNotesCount,
            'total_gross_profit'  => $totalCharged
                ? ($totalCharged - $totalCost) / ($totalCharged) * 100
                : 0,
            'accounts_receivable' => $invoicesTotalAmount,
            'chart'               => $this->getChart($filter, $invoices->groupBy(function (Invoice $invoice) {
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
        return array_merge($currentPeriodData, [
            'total_revenue_change'       => $this->compare($currentPeriodData, $previousPeriodData, 'total_revenue'),
            'from_jobs_change'           => $this->compare($currentPeriodData, $previousPeriodData, 'from_jobs'),
            'invoices_change'            => $this->compare($currentPeriodData, $previousPeriodData, 'invoices'),
            'purchase_orders_change'     => $this->compare($currentPeriodData, $previousPeriodData, 'purchase_orders'),
            'credit_notes_change'        => $this->compare($currentPeriodData, $previousPeriodData, 'credit_notes'),
            'total_gross_profit_change'  => $this->compare(
                $currentPeriodData,
                $previousPeriodData,
                'total_gross_profit'
            ),
            'accounts_receivable_change' => $this->compare(
                $currentPeriodData,
                $previousPeriodData,
                'accounts_receivable'
            ),
            'previous_interval_chart'    => $previousPeriodData['chart'],
        ]);
    }
}
