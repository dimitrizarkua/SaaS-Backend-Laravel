<?php

namespace App\Components\Reporting\Services;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Reporting\Interfaces\CostingSummaryInterface;
use App\Components\Reporting\Models\UsageCosts;
use App\Models\HasLatestStatus;
use Illuminate\Support\Collection;

/**
 * Class CostingSummaryService
 *
 * @package App\Components\Reporting\Services
 */
class CostingSummaryService implements CostingSummaryInterface
{
    use UsageCosts, HasLatestStatus;

    /**
     * {@inheritdoc}
     */
    public function getSummary(int $jobId): array
    {
        $invoices = $this->getFinancialEntities(Invoice::class, $jobId);
        $invoices->each(function (Invoice $invoice) {
            return $invoice->total_amount = $invoice->getTotalAmount();
        });
        $invoicesTotalAmount = $invoices->sum('total_amount');

        $creditNotes = $this->getFinancialEntities(CreditNote::class, $jobId);
        $creditNotes->each(function (CreditNote $creditNote) {
            return $creditNote->total_amount = $creditNote->getTotalAmount();
        });
        $creditNotesTotalAmount = $creditNotes->sum('total_amount');

        $purchaseOrders = $this->getFinancialEntities(PurchaseOrder::class, $jobId);
        $purchaseOrders->each(function (PurchaseOrder $purchaseOrder) {
            return $purchaseOrder->total_amount = $purchaseOrder->getTotalAmount();
        });
        $purchaseOrdersTotalAmount = $purchaseOrders->sum('total_amount');

        $totalCharged = $invoicesTotalAmount - $creditNotesTotalAmount;

        $labourTotalAmount            = $this->getLaboursTotalCost([$jobId]);
        $materialsTotalAmount         = $this->getMaterialTotalCost([$jobId]);
        $equipmentTotalAmount         = $this->getEquipmentTotalCost([$jobId]);
        $reimbursementTotalAmount     = $this->getReimbursementTotalCost([$jobId]);
        $lahaCompensationTotalAmount  = $this->getLahaCompensationTotalCost([$jobId]);
        $assessmentReportsTotalAmount = $this->getAssessmentReportsAmount([$jobId]);

        $totalCost = $labourTotalAmount
            + $materialsTotalAmount
            + $equipmentTotalAmount
            + $purchaseOrdersTotalAmount
            + $reimbursementTotalAmount
            + $lahaCompensationTotalAmount;

        $assessmentReports = AssessmentReport::whereJobId($jobId)
            ->get()
            ->map(function (AssessmentReport $assessmentReport) {
                return [
                    'id'           => $assessmentReport->id,
                    'date'         => $assessmentReport->date->toDateString(),
                    'report'       => $assessmentReport->heading,
                    'total_amount' => $assessmentReport->getTotalAmount(),
                    'status'       => $assessmentReport->latestStatus()->first()->status,
                ];
            })
            ->toArray();

        $result = [
            'total_costed'       => $totalCost,
            'remaining'          => $assessmentReportsTotalAmount - $totalCharged,
            'gross_profit'       => $totalCharged
                ? ($totalCharged - $totalCost) / ($totalCharged) * 100
                : 0,
            'labour_used'        => $labourTotalAmount,
            'equipment_used'     => $equipmentTotalAmount,
            'materials_used'     => $materialsTotalAmount,
            'po_and_other_used'  => $purchaseOrdersTotalAmount
                + $reimbursementTotalAmount
                + $lahaCompensationTotalAmount,
            'assessment_reports' => $assessmentReports,
        ];

        return $result;
    }

    /**
     * Returns list of financial entities by job.
     *
     * @param string $className
     * @param int    $jobId
     *
     * @return \Illuminate\Support\Collection
     */
    private function getFinancialEntities(string $className, int $jobId): Collection
    {
        /** @var \App\Components\Finance\Models\FinancialEntity $className */
        $entitiesIds = $className::query()
            ->select(['id'])
            ->where('job_id', $jobId)
            ->orderByDesc('id')
            ->pluck('id')
            ->toArray();

        $result = $className::getCollectionByStatuses(
            $entitiesIds,
            [FinancialEntityStatuses::APPROVED],
            ['items.taxRate']
        );

        return $result;
    }
}
