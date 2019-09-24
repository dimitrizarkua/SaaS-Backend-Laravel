<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\VO\AssessmentReportCostItemData;
use App\Components\AssessmentReports\Services\AssessmentReportCostItemsService;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\TaxRate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportCostItemsServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportCostItemsServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportCostItemsService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportCostItemsService::class);
    }

    public function testGetAssessmentReportCostItems()
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        factory(AssessmentReportCostItem::class, $count)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $reloaded = $this->service->getEntities($assessmentReport->id);

        self::assertCount($count, $reloaded);
    }

    public function testGetAssessmentReportCostItem()
    {
        /** @var AssessmentReportCostItem $item */
        $item = factory(AssessmentReportCostItem::class)->create();

        $reloaded = $this->service->getEntity($item->assessment_report_id, $item->id);

        self::compareDataWithModel($item->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportCostItem()
    {
        $stage = $this->fakeAssessmentReportCostingStage();
        $data  = new AssessmentReportCostItemData([
            'assessment_report_costing_stage_id' => $stage->id,
            'gs_code_id'                         => factory(GSCode::class)->create()->id,
            'position'                           => $this->faker->numberBetween(1, 100),
            'description'                        => $this->faker->words(3, true),
            'quantity'                           => $this->faker->numberBetween(1, 10),
            'unit_cost'                          => $this->faker->randomFloat(2, 1, 500),
            'discount'                           => $this->faker->randomFloat(2, 1, 100),
            'markup'                             => $this->faker->randomFloat(2, 1, 200),
            'tax_rate_id'                        => factory(TaxRate::class)->create()->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $section = $this->service->create($data, $stage->assessment_report_id);

        self::compareDataWithModel($data->toArray(), $section);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportCostItemWhenAssessmentReportIsApproved()
    {
        $stage = $this->fakeAssessmentReportCostingStage(AssessmentReportStatuses::CLIENT_APPROVED);
        $data  = new AssessmentReportCostItemData([
            'assessment_report_costing_stage_id' => $stage->id,
            'gs_code_id'                         => factory(GSCode::class)->create()->id,
            'position'                           => $this->faker->numberBetween(1, 100),
            'description'                        => $this->faker->words(3, true),
            'quantity'                           => $this->faker->numberBetween(1, 10),
            'unit_cost'                          => $this->faker->randomFloat(2, 1, 500),
            'discount'                           => $this->faker->randomFloat(2, 1, 100),
            'markup'                             => $this->faker->randomFloat(2, 1, 200),
            'tax_rate_id'                        => factory(TaxRate::class)->create()->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $stage->assessment_report_id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportCostItem()
    {
        $stage = $this->fakeAssessmentReportCostingStage();
        /** @var  AssessmentReportCostItem $item */
        $item = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $stage->assessment_report_id,
        ]);
        $data = new AssessmentReportCostItemData([
            'assessment_report_costing_stage_id' => $stage->id,
            'gs_code_id'                         => factory(GSCode::class)->create()->id,
            'position'                           => $this->faker->numberBetween(1, 100),
            'description'                        => $this->faker->words(3, true),
            'quantity'                           => $this->faker->numberBetween(1, 10),
            'unit_cost'                          => $this->faker->randomFloat(2, 1, 500),
            'discount'                           => $this->faker->randomFloat(2, 1, 100),
            'markup'                             => $this->faker->randomFloat(2, 1, 200),
            'tax_rate_id'                        => factory(TaxRate::class)->create()->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->service->update($data, $item->assessment_report_id, $item->id);

        $reloaded = AssessmentReportCostItem::findOrFail($item->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportCostItemWhenAssessmentReportIsApproved()
    {
        $stage = $this->fakeAssessmentReportCostingStage(AssessmentReportStatuses::CLIENT_APPROVED);
        /** @var  AssessmentReportCostItem $item */
        $item = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $stage->assessment_report_id,
        ]);
        $data = new AssessmentReportCostItemData([
            'assessment_report_costing_stage_id' => $stage->id,
            'gs_code_id'                         => factory(GSCode::class)->create()->id,
            'position'                           => $this->faker->numberBetween(1, 100),
            'description'                        => $this->faker->words(3, true),
            'quantity'                           => $this->faker->numberBetween(1, 10),
            'unit_cost'                          => $this->faker->randomFloat(2, 1, 500),
            'discount'                           => $this->faker->randomFloat(2, 1, 100),
            'markup'                             => $this->faker->randomFloat(2, 1, 200),
            'tax_rate_id'                        => factory(TaxRate::class)->create()->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update($data, $item->assessment_report_id, $item->id);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportCostItem()
    {
        $stage = $this->fakeAssessmentReportCostingStage();
        /** @var  AssessmentReportCostItem $item */
        $item = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $stage->assessment_report_id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->service->delete($item->assessment_report_id, $item->id);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportCostItem::findOrFail($item->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailToDeleteAssessmentReportCostItemWhenAssessmentReportIsApproved()
    {
        $stage = $this->fakeAssessmentReportCostingStage(AssessmentReportStatuses::CLIENT_APPROVED);
        /** @var  AssessmentReportCostItem $item */
        $item = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $stage->assessment_report_id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete($item->assessment_report_id, $item->id);
    }
}
