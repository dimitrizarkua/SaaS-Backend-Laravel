<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\TaxRate;
use App\Http\Responses\AssessmentReports\AssessmentReportCostItemResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportCostItemsControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportCostItemsControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.view',
        'assessment_reports.manage',
    ];

    public function testGetAssessmentReportCostItems()
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        factory(AssessmentReportCostItem::class, $count)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $url = action('AssessmentReports\AssessmentReportCostItemsController@index', [
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetAssessmentReportCostItem()
    {
        /** @var AssessmentReportCostItem $model */
        $model = factory(AssessmentReportCostItem::class)->create();
        $url   = action('AssessmentReports\AssessmentReportCostItemsController@show', [
            'assessment_report_id' => $model->assessment_report_id,
            'costing_stage_id'     => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportCostItemResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportCostItem()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $request          = [
            'assessment_report_costing_stage_id' => factory(AssessmentReportCostingStage::class)->create()->id,
            'gs_code_id'                         => factory(GSCode::class)->create()->id,
            'position'                           => $this->faker->numberBetween(1, 100),
            'description'                        => $this->faker->words(3, true),
            'quantity'                           => $this->faker->numberBetween(1, 10),
            'unit_cost'                          => $this->faker->randomFloat(2, 1, 500),
            'discount'                           => $this->faker->randomFloat(2, 1, 100),
            'markup'                             => $this->faker->randomFloat(2, 1, 200),
            'tax_rate_id'                        => factory(TaxRate::class)->create()->id,
        ];
        $url              = action('AssessmentReports\AssessmentReportCostItemsController@store', [
            'assessment_report_id' => $assessmentReport->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportCostItemResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportCostItem::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportCostItem()
    {
        $stage = $this->fakeAssessmentReportCostingStage();
        /** @var  AssessmentReportCostItem $item */
        $item    = factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $stage->assessment_report_id,
        ]);
        $request = [
            'assessment_report_costing_stage_id' => $stage->id,
            'gs_code_id'                         => factory(GSCode::class)->create()->id,
            'position'                           => $this->faker->numberBetween(1, 100),
            'description'                        => $this->faker->words(3, true),
            'quantity'                           => $this->faker->numberBetween(1, 10),
            'unit_cost'                          => $this->faker->randomFloat(2, 1, 500),
            'discount'                           => $this->faker->randomFloat(2, 1, 100),
            'markup'                             => $this->faker->randomFloat(2, 1, 200),
            'tax_rate_id'                        => factory(TaxRate::class)->create()->id,
        ];
        $url     = action('AssessmentReports\AssessmentReportCostItemsController@update', [
            'assessment_report_id' => $item->assessment_report_id,
            'cost_item_id'         => $item->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportCostItem::findOrFail($item->id);

        self::compareDataWithModel($request, $reloaded);
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
        $url  = action('AssessmentReports\AssessmentReportCostItemsController@destroy', [
            'assessment_report_id' => $item->assessment_report_id,
            'cost_item_id'         => $item->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportCostItem::findOrFail($item->id);
    }
}
