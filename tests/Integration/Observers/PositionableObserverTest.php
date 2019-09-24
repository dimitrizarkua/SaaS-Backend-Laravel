<?php

namespace Tests\Integration\Observers;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;
use App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use Tests\TestCase;

/**
 * Class PositionableObserverTest
 *
 * @package Tests\Integration\Observers
 * @group   observers
 * @group   assessment-reports
 * @group   finance
 * @group   purchase-orders
 * @group   credit-notes
 * @group   invoices
 */
class PositionableObserverTest extends TestCase
{
    public function testAssessmentReportSectionsShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        /** @var AssessmentReportSection $first */
        $first = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportSection $second */
        $second = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportSection $third */
        $third = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportSection $fourth */
        $fourth = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);

        $reloaded = AssessmentReportSection::whereAssessmentReportId($assessmentReport->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportSectionsShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        /** @var AssessmentReportSection $first */
        $first = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportSection $second */
        $second = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 2,
        ]);
        /** @var AssessmentReportSection $third */
        $third = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 3,
        ]);
        /** @var AssessmentReportSection $fourth */
        $fourth = factory(AssessmentReportSection::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 2,
        ]);

        $reloaded = AssessmentReportSection::whereAssessmentReportId($assessmentReport->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportCostingStagesShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        /** @var AssessmentReportCostingStage $first */
        $first = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostingStage $second */
        $second = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostingStage $third */
        $third = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostingStage $fourth */
        $fourth = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);

        $reloaded = AssessmentReportCostingStage::whereAssessmentReportId($assessmentReport->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportCostingStagesShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        /** @var AssessmentReportCostingStage $first */
        $first = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostingStage $second */
        $second = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 2,
        ]);
        /** @var AssessmentReportCostingStage $third */
        $third = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 3,
        ]);
        /** @var AssessmentReportCostingStage $fourth */
        $fourth = factory(AssessmentReportCostingStage::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 2,
        ]);

        $reloaded = AssessmentReportCostingStage::whereAssessmentReportId($assessmentReport->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportCostItemsShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        /** @var AssessmentReportCostItem $first */
        $first = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostItem $second */
        $second = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostItem $third */
        $third = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostItem $fourth */
        $fourth = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);

        $reloaded = AssessmentReportCostItem::whereAssessmentReportId($assessmentReport->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportCostItemsShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        /** @var AssessmentReportCostItem $first */
        $first = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 1,
        ]);
        /** @var AssessmentReportCostItem $second */
        $second = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 2,
        ]);
        /** @var AssessmentReportCostItem $third */
        $third = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 3,
        ]);
        /** @var AssessmentReportCostItem $fourth */
        $fourth = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
            'position'             => 2,
        ]);

        $reloaded = AssessmentReportCostItem::whereAssessmentReportId($assessmentReport->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportTextBlocksShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create();
        /** @var AssessmentReportSectionTextBlock $first */
        $first = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionTextBlock $second */
        $second = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionTextBlock $third */
        $third = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionTextBlock $fourth */
        $fourth = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);

        $reloaded = AssessmentReportSectionTextBlock::whereAssessmentReportSectionId($assessmentReportSection->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportTextBlocksShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create();
        /** @var AssessmentReportSectionTextBlock $first */
        $first = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionTextBlock $second */
        $second = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 2,
        ]);
        /** @var AssessmentReportSectionTextBlock $third */
        $third = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 3,
        ]);
        /** @var AssessmentReportSectionTextBlock $fourth */
        $fourth = factory(AssessmentReportSectionTextBlock::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 3,
        ]);

        $reloaded = AssessmentReportSectionTextBlock::whereAssessmentReportSectionId($assessmentReportSection->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportPhotosShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create();
        /** @var AssessmentReportSectionPhoto $first */
        $first = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionPhoto $second */
        $second = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionPhoto $third */
        $third = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionPhoto $fourth */
        $fourth = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);

        $reloaded = AssessmentReportSectionPhoto::whereAssessmentReportSectionId($assessmentReportSection->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportPhotosShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create();
        /** @var AssessmentReportSectionPhoto $first */
        $first = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionPhoto $second */
        $second = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 2,
        ]);
        /** @var AssessmentReportSectionPhoto $third */
        $third = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 3,
        ]);
        /** @var AssessmentReportSectionPhoto $fourth */
        $fourth = factory(AssessmentReportSectionPhoto::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 2,
        ]);

        $reloaded = AssessmentReportSectionPhoto::whereAssessmentReportSectionId($assessmentReportSection->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testAssessmentReportSectionCostItemsShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create();
        /** @var AssessmentReportSectionCostItem $first */
        $first = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionCostItem $second */
        $second = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionCostItem $third */
        $third = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionCostItem $fourth */
        $fourth = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);

        $reloaded = AssessmentReportSectionCostItem::whereAssessmentReportSectionId($assessmentReportSection->id)
            ->get();

        $positionForFirstEntity  = $reloaded
            ->where('assessment_report_cost_item_id', $first->assessment_report_cost_item_id)
            ->first()
            ->position;
        $positionForSecondEntity = $reloaded
            ->where('assessment_report_cost_item_id', $second->assessment_report_cost_item_id)
            ->first()
            ->position;
        $positionForThirdEntity  = $reloaded
            ->where('assessment_report_cost_item_id', $third->assessment_report_cost_item_id)
            ->first()
            ->position;
        $positionForFourthEntity = $reloaded
            ->where('assessment_report_cost_item_id', $fourth->assessment_report_cost_item_id)
            ->first()
            ->position;

        self::assertEquals(4, $positionForFirstEntity);
        self::assertEquals(3, $positionForSecondEntity);
        self::assertEquals(2, $positionForThirdEntity);
        self::assertEquals(1, $positionForFourthEntity);
    }

    public function testAssessmentReportSectionCostItemsShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var AssessmentReportSection $assessmentReportSection */
        $assessmentReportSection = factory(AssessmentReportSection::class)->create();
        /** @var AssessmentReportSectionCostItem $first */
        $first = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 1,
        ]);
        /** @var AssessmentReportSectionCostItem $second */
        $second = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 2,
        ]);
        /** @var AssessmentReportSectionCostItem $third */
        $third = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 3,
        ]);
        /** @var AssessmentReportSectionCostItem $fourth */
        $fourth = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $assessmentReportSection->id,
            'position'                     => 3,
        ]);

        $reloaded = AssessmentReportSectionCostItem::whereAssessmentReportSectionId($assessmentReportSection->id)
            ->get();

        $positionForFirstEntity  = $reloaded
            ->where('assessment_report_cost_item_id', $first->assessment_report_cost_item_id)
            ->first()
            ->position;
        $positionForSecondEntity = $reloaded
            ->where('assessment_report_cost_item_id', $second->assessment_report_cost_item_id)
            ->first()
            ->position;
        $positionForThirdEntity  = $reloaded
            ->where('assessment_report_cost_item_id', $third->assessment_report_cost_item_id)
            ->first()
            ->position;
        $positionForFourthEntity = $reloaded
            ->where('assessment_report_cost_item_id', $fourth->assessment_report_cost_item_id)
            ->first()
            ->position;

        self::assertEquals(1, $positionForFirstEntity);
        self::assertEquals(2, $positionForSecondEntity);
        self::assertEquals(4, $positionForThirdEntity);
        self::assertEquals(3, $positionForFourthEntity);
    }

    public function testInvoiceItemsShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        /** @var InvoiceItem $first */
        $first = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 1,
        ]);
        /** @var InvoiceItem $second */
        $second = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 1,
        ]);
        /** @var InvoiceItem $third */
        $third = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 1,
        ]);
        /** @var InvoiceItem $fourth */
        $fourth = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 1,
        ]);

        $reloaded = InvoiceItem::query()
            ->where('invoice_id', $invoice->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testInvoiceItemsShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create();
        /** @var InvoiceItem $first */
        $first = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 1,
        ]);
        /** @var InvoiceItem $second */
        $second = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 2,
        ]);
        /** @var InvoiceItem $third */
        $third = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 3,
        ]);
        /** @var InvoiceItem $fourth */
        $fourth = factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
            'position'   => 3,
        ]);

        $reloaded = InvoiceItem::query()
            ->where('invoice_id', $invoice->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testCreditNoteItemsShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create();
        /** @var CreditNoteItem $first */
        $first = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 1,
        ]);
        /** @var CreditNoteItem $second */
        $second = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 1,
        ]);
        /** @var CreditNoteItem $third */
        $third = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 1,
        ]);
        /** @var CreditNoteItem $fourth */
        $fourth = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 1,
        ]);

        $reloaded = CreditNoteItem::query()
            ->where('credit_note_id', $creditNote->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testCreditNoteItemsShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var CreditNote $creditNote */
        $creditNote = factory(CreditNote::class)->create();
        /** @var CreditNoteItem $first */
        $first = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 1,
        ]);
        /** @var CreditNoteItem $second */
        $second = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 2,
        ]);
        /** @var CreditNoteItem $third */
        $third = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 3,
        ]);
        /** @var CreditNoteItem $fourth */
        $fourth = factory(CreditNoteItem::class)->create([
            'credit_note_id' => $creditNote->id,
            'position'       => 3,
        ]);

        $reloaded = CreditNoteItem::query()
            ->where('credit_note_id', $creditNote->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testPurchaseOrderItemsShouldBeInRightOrderWhenAddNewOnesToStartOfList(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var PurchaseOrderItem $first */
        $first = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 1,
        ]);
        /** @var PurchaseOrderItem $second */
        $second = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 1,
        ]);
        /** @var PurchaseOrderItem $third */
        $third = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 1,
        ]);
        /** @var PurchaseOrderItem $fourth */
        $fourth = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 1,
        ]);

        $reloaded = PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->get();

        self::assertEquals(4, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(1, $reloaded->where('id', $fourth->id)->first()->position);
    }

    public function testPurchaseOrderItemsShouldBeInRightOrderWhenAddNewOnesToList(): void
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create();
        /** @var PurchaseOrderItem $first */
        $first = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 1,
        ]);
        /** @var PurchaseOrderItem $second */
        $second = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 2,
        ]);
        /** @var PurchaseOrderItem $third */
        $third = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 3,
        ]);
        /** @var PurchaseOrderItem $fourth */
        $fourth = factory(PurchaseOrderItem::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'position'          => 3,
        ]);

        $reloaded = PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchaseOrder->id)
            ->get();

        self::assertEquals(1, $reloaded->where('id', $first->id)->first()->position);
        self::assertEquals(2, $reloaded->where('id', $second->id)->first()->position);
        self::assertEquals(4, $reloaded->where('id', $third->id)->first()->position);
        self::assertEquals(3, $reloaded->where('id', $fourth->id)->first()->position);
    }
}
