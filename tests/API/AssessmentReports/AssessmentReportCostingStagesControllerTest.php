<?php

namespace Tests\API\AssessmentReports;

use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Http\Responses\AssessmentReports\AssessmentReportCostingStageResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AssessmentReportCostingStagesControllerTest
 *
 * @package Tests\API\AssessmentReports
 * @group   assessment-reports
 * @group   api
 */
class AssessmentReportCostingStagesControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.view',
        'assessment_reports.manage',
    ];

    public function testGetAssessmentReportCostingStages()
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        factory(AssessmentReportCostingStage::class, $count)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $url = action('AssessmentReports\AssessmentReportCostingStagesController@index', [
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testGetAssessmentReportCostingStage()
    {
        /** @var AssessmentReportCostingStage $model */
        $model = factory(AssessmentReportCostingStage::class)->create();
        $url   = action('AssessmentReports\AssessmentReportCostingStagesController@show', [
            'assessment_report_id' => $model->assessment_report_id,
            'costing_stage_id'     => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportCostingStageResponse::class, true);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
    }

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReportCostingStage()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $request          = [
            'name'     => $this->faker->word,
            'position' => $this->faker->numberBetween(1, 100),
        ];
        $url              = action('AssessmentReports\AssessmentReportCostingStagesController@store', [
            'assessment_report_id' => $assessmentReport->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportCostingStageResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReportCostingStage::findOrFail($data['id']);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReportCostingStage()
    {
        $stage   = $this->fakeAssessmentReportCostingStage();
        $request = [
            'name'     => $this->faker->word,
            'position' => $this->faker->numberBetween(1, 100),
        ];
        $url     = action('AssessmentReports\AssessmentReportCostingStagesController@update', [
            'assessment_report_id' => $stage->assessment_report_id,
            'costing_stage_id'     => $stage->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReportCostingStage::findOrFail($stage->id);

        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportCostingStage()
    {
        $stage = $this->fakeAssessmentReportCostingStage();
        $url   = action('AssessmentReports\AssessmentReportCostingStagesController@destroy', [
            'assessment_report_id' => $stage->assessment_report_id,
            'costing_stage_id'     => $stage->id,
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->deleteJson($url)
            ->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportCostingStage::findOrFail($stage->id);
    }
}
