<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportSectionEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Components\AssessmentReports\Models\VO\AssessmentReportSectionCostItemData;
use App\Components\AssessmentReports\Services\AssessmentReportSectionCostItemsService;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportSectionCostItemsServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportSectionCostItemsServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportSectionCostItemsService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportSectionCostItemsService::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportSectionCostItem()
    {
        /** @var AssessmentReportCostItem $costItem */
        $costItem = factory(AssessmentReportCostItem::class)->create();
        $section  = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::COSTS);
        $data     = new AssessmentReportSectionCostItemData([
            'assessment_report_cost_item_id' => $costItem->id,
            'position'                       => $this->faker->numberBetween(1, 100),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $sectionCostItem = $this->service->create($data, $section->assessment_report_id, $section->id);

        self::assertEquals($section->id, $sectionCostItem->assessment_report_section_id);
        self::compareDataWithModel($data->toArray(), $sectionCostItem);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionCostItemWhenAssessmentReportIsApproved()
    {
        /** @var AssessmentReportCostItem $costItem */
        $costItem = factory(AssessmentReportCostItem::class)->create();
        $section  = $this->fakeAssessmentReportSection(null, AssessmentReportStatuses::CLIENT_APPROVED);
        $data     = new AssessmentReportSectionCostItemData([
            'assessment_report_cost_item_id' => $costItem->id,
            'position'                       => $this->faker->numberBetween(1, 100),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportSectionCostItemWhenSectionIsNonCosts()
    {
        /** @var AssessmentReportCostItem $costItem */
        $costItem = factory(AssessmentReportCostItem::class)->create();
        $section  = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        $data     = new AssessmentReportSectionCostItemData([
            'assessment_report_cost_item_id' => $costItem->id,
            'position'                       => $this->faker->numberBetween(1, 100),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $section->assessment_report_id, $section->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportSectionCostItem()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        /** @var AssessmentReportSectionCostItem $sectionCostItem */
        $sectionCostItem = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data            = new AssessmentReportSectionCostItemData([
            'position' => $this->faker->numberBetween(1, 100),
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->update(
            $data,
            $sectionCostItem->section->assessment_report_id,
            $sectionCostItem->assessment_report_section_id,
            $sectionCostItem->assessment_report_cost_item_id
        );

        $reloaded = AssessmentReportSectionCostItem::query()
            ->where('assessment_report_section_id', $sectionCostItem->assessment_report_section_id)
            ->where('assessment_report_cost_item_id', $sectionCostItem->assessment_report_cost_item_id)
            ->first();

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportSectionCostItemWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::PHOTOS,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionCostItem $sectionCostItem */
        $sectionCostItem = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);
        $data            = new AssessmentReportSectionCostItemData([
            'position' => $this->faker->numberBetween(1, 100),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update(
            $data,
            $sectionCostItem->section->assessment_report_id,
            $sectionCostItem->assessment_report_section_id,
            $sectionCostItem->assessment_report_cost_item_id
        );
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportSectionCostItem()
    {
        $section = $this->fakeAssessmentReportSection(AssessmentReportSectionTypes::PHOTOS);
        /** @var AssessmentReportSectionCostItem $sectionCostItem */
        $sectionCostItem = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectsEvents(AssessmentReportSectionEntityUpdated::class);
        $this->service->delete(
            $sectionCostItem->section->assessment_report_id,
            $sectionCostItem->assessment_report_section_id,
            $sectionCostItem->assessment_report_cost_item_id
        );
        $reloaded = AssessmentReportSectionCostItem::query()
            ->where('assessment_report_section_id', $sectionCostItem->assessment_report_section_id)
            ->where('assessment_report_cost_item_id', $sectionCostItem->assessment_report_cost_item_id)
            ->first();

        self::assertNull($reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportSectionCostItemWhenAssessmentReportIsApproved()
    {
        $section = $this->fakeAssessmentReportSection(
            AssessmentReportSectionTypes::PHOTOS,
            AssessmentReportStatuses::CLIENT_APPROVED
        );
        /** @var AssessmentReportSectionCostItem $sectionCostItem */
        $sectionCostItem = factory(AssessmentReportSectionCostItem::class)->create([
            'assessment_report_section_id' => $section->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete(
            $sectionCostItem->section->assessment_report_id,
            $sectionCostItem->assessment_report_section_id,
            $sectionCostItem->assessment_report_cost_item_id
        );
    }
}
