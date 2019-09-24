<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Http\Responses\AssessmentReports\AssessmentReportSectionCostItemResponse;

/**
 * Class AssessmentReportSectionCostItemsControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportSectionCostItemsControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.manage',
    ];

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionCostItem()
    {
        /** @var AssessmentReportCostItem $costItem */
        $costItem = factory(AssessmentReportCostItem::class)->create();
        $section  = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::COSTS);
        $request  = [
            'assessment_report_cost_item_id' => $costItem->id,
            'position'                       => $this->faker->numberBetween(1, 100),
        ];
        $url      = action('AssessmentReports\AssessmentReportSectionCostItemsController@store', [
            'assessment_report_id' => $section->assessment_report_id,
            'section_id'           => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportSectionCostItemResponse::class, true);
        $reloaded = AssessmentReportSectionCostItem::query()
            ->where('assessment_report_section_id', $section->id)
            ->where('assessment_report_cost_item_id', $costItem->id)
            ->first();

        self::assertEquals($section->id, $reloaded->assessment_report_section_id);
        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionCostItem()
    {
        /** @var AssessmentReportCostItem $costItem */
        $costItem = factory(AssessmentReportCostItem::class)->create();
        $section  = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::COSTS);
        /** @var AssessmentReportSectionCostItem $model */
        $model   = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_cost_item_id' => $costItem->id,
            'assessment_report_section_id'   => $section->id,
        ]);
        $request = [
            'assessment_report_cost_item_id' => $costItem->id,
            'position'                       => $this->faker->numberBetween(1, 100),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionCostItemsController@update', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportSectionCostItem::query()
            ->where('assessment_report_section_id', $section->id)
            ->where('assessment_report_cost_item_id', $costItem->id)
            ->first();

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionCostItem()
    {
        /** @var AssessmentReportCostItem $costItem */
        $costItem = factory(AssessmentReportCostItem::class)->create();
        $section  = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::COSTS);
        /** @var AssessmentReportSectionCostItem $model */
        $model   = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_cost_item_id' => $costItem->id,
            'assessment_report_section_id'   => $section->id,
        ]);
        $request = [
            'assessment_report_cost_item_id' => $costItem->id,
            'position'                       => $this->faker->numberBetween(1, 100),
        ];
        $url     = action('AssessmentReports\AssessmentReportSectionCostItemsController@destroy', [
            'assessment_report_id' => $model->section->assessment_report_id,
            'section_id'           => $model->section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->deleteJson($url, $request)
            ->assertStatus(200);

        $reloaded = AssessmentReportSectionCostItem::query()
            ->where('assessment_report_section_id', $section->id)
            ->where('assessment_report_cost_item_id', $costItem->id)
            ->first();

        self::assertNull($reloaded);
    }
}
