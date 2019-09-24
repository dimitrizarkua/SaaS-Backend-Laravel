<?php

namespace App\Components\Reporting\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportStatus;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\CreditNoteStatus;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\Models\PurchaseOrderStatus;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Reporting\Interfaces\CostingSummaryInterface;
use App\Helpers\Decimal;
use Illuminate\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class CostingSummaryServiceTest
 *
 * @package Tests\Unit\Reporting\Services
 * @group   usage-and-actuals
 * @group   reporting
 */
class CostingSummaryServiceTest extends TestCase
{
    /**
     * @var \App\Components\Reporting\Interfaces\CostingSummaryInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = Container::getInstance()
            ->make(CostingSummaryInterface::class);
    }

    public function testGetSummaryWithData()
    {
        $job = factory(Job::class)->create([
            'created_at' => Carbon::now()->subMonth(),
        ]);
        /** @var JobLabour $jobLabour */
        $jobLabour = factory(JobLabour::class)->create([
            'job_id' => $job->id,
        ]);
        /** @var JobMaterial $jobMaterial */
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id' => $job->id,
        ]);
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        /** @var JobReimbursement $jobReimbursement */
        $jobReimbursement = factory(JobReimbursement::class)->create([
            'job_id'        => $job->id,
            'is_chargeable' => true,
        ]);
        /** @var JobLahaCompensation $jobLahaCompensation */
        $jobLahaCompensation = factory(JobLahaCompensation::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        /** @var \Illuminate\Support\Collection $invoices */
        $invoices = factory(Invoice::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
            ])
            ->each(function (Invoice $invoice) {
                factory(InvoiceItem::class, 3)->create([
                    'invoice_id' => $invoice->id,
                ]);
                factory(InvoiceStatus::class)->create([
                    'invoice_id' => $invoice->id,
                    'status'     => FinancialEntityStatuses::APPROVED,
                ]);
            });
        /** @var \Illuminate\Support\Collection $creditNotes */
        $creditNotes = factory(CreditNote::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
                'date'   => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (CreditNote $creditNote) {
                factory(CreditNoteItem::class, 3)->create([
                    'credit_note_id' => $creditNote->id,
                ]);
                factory(CreditNoteStatus::class)->create([
                    'credit_note_id' => $creditNote->id,
                    'status'         => FinancialEntityStatuses::APPROVED,
                ]);
            });
        /** @var \Illuminate\Support\Collection $purchaseOrders */
        $purchaseOrders = factory(PurchaseOrder::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
                'date'   => Carbon::now()->addDays($this->faker->numberBetween(1, 5)),
            ])
            ->each(function (PurchaseOrder $purchaseOrder) {
                factory(PurchaseOrderItem::class, 3)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                ]);
                factory(PurchaseOrderStatus::class)->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'status'            => FinancialEntityStatuses::APPROVED,
                ]);
            });

        /** @var \Illuminate\Support\Collection $assessmentReports */
        $assessmentReports       = factory(AssessmentReport::class, $this->faker->numberBetween(2, 4))
            ->create([
                'job_id' => $job->id,
            ])
            ->each(function (AssessmentReport $assessmentReport) {
                factory(AssessmentReportCostItem::class, 3)->create([
                    'assessment_report_id' => $assessmentReport->id,
                ]);
                factory(AssessmentReportStatus::class)->create([
                    'assessment_report_id' => $assessmentReport->id,
                    'status'               => AssessmentReportStatuses::CLIENT_APPROVED,
                ]);
            });
        $invoicesAmount          = InvoiceItem::query()
            ->leftJoin('tax_rates', 'invoice_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->sum(DB::raw('(unit_cost * (1 - (discount / 100))) * (1 + tax_rates.rate) * quantity'));
        $creditNotesAmount       = CreditNoteItem::query()
            ->leftJoin('tax_rates', 'credit_note_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('credit_note_id', $creditNotes->pluck('id'))
            ->sum(DB::raw('unit_cost * (1 + tax_rates.rate) * quantity'));
        $purchaseOrdersAmount    = PurchaseOrderItem::query()
            ->leftJoin('tax_rates', 'purchase_order_items.tax_rate_id', '=', 'tax_rates.id')
            ->whereIn('purchase_order_id', $purchaseOrders->pluck('id'))
            ->sum(DB::raw('(unit_cost * (1 + (markup / 100))) * (1 + tax_rates.rate) * quantity'));
        $assessmentReportsAmount = $assessmentReports->reduce(
            function (float $carry, AssessmentReport $assessmentReport) {
                return $carry + $assessmentReport->getTotalAmount() + $assessmentReport->getTax();
            },
            0
        );

        $totalCharged = $invoicesAmount - $creditNotesAmount;

        $jobLabourAmount     = $jobLabour->calculateTotalAmount();
        $jobMaterialAmount   = $jobMaterial->buy_cost_per_unit * $jobMaterial->quantity_used_override;
        $jobEquipmentAmount  = $jobEquipment->buy_cost_per_interval * $jobEquipment->intervals_count_override;
        $jobLahaCompensation = $jobLahaCompensation->rate_per_day * $jobLahaCompensation->days;
        $totalCost           = $jobLabourAmount
            + $jobMaterialAmount
            + $jobEquipmentAmount
            + $purchaseOrdersAmount
            + $jobReimbursement->total_amount
            + $jobLahaCompensation;


        $result = $this->service->getSummary($job->id);

        self::assertEquals($totalCost, $result['total_costed']);
        self::assertEquals($assessmentReportsAmount - $totalCharged, $result['remaining']);
        self::assertEquals(
            $totalCharged
                ? ($totalCharged - $totalCost) / $totalCharged * 100
                : 0,
            $result['gross_profit']
        );
        self::assertEquals($jobLabourAmount, $result['labour_used']);
        self::assertEquals($jobEquipmentAmount, $result['equipment_used']);
        self::assertEquals($jobMaterialAmount, $result['materials_used']);
        self::assertEquals(
            $purchaseOrdersAmount
            + $jobReimbursement->total_amount
            + $jobLahaCompensation,
            $result['po_and_other_used']
        );
    }

    public function testGetSummaryWithoutData()
    {
        $job    = factory(Job::class)->create([
            'created_at' => Carbon::now()->subMonth(),
        ]);
        $result = $this->service->getSummary($job->id);

        self::assertEquals(0, $result['total_costed']);
        self::assertEquals(0, $result['remaining']);
        self::assertEquals(0, $result['gross_profit']);
        self::assertEquals(0, $result['labour_used']);
        self::assertEquals(0, $result['equipment_used']);
        self::assertEquals(0, $result['materials_used']);
        self::assertEquals(0, $result['po_and_other_used']);
        self::assertEquals([], $result['assessment_reports']);
    }
}
