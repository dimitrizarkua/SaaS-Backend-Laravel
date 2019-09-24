<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportEntityUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\VO\AssessmentReportCostingStageData;
use App\Components\AssessmentReports\Services\AssessmentReportCostingStagesService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportCostingStagesServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportCostingStagesServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportCostingStagesService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportCostingStagesService::class);
    }

    public function testGetAssessmentReportCostingStages()
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();
        factory(AssessmentReportCostingStage::class, $count)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $reloaded = $this->service->getEntities($assessmentReport->id);

        self::assertCount($count, $reloaded);
    }

    public function testGetAssessmentReportCostingStage()
    {
        /** @var AssessmentReportCostingStage $stage */
        $stage = factory(AssessmentReportCostingStage::class)->create();

        $reloaded = $this->service->getEntity($stage->assessment_report_id, $stage->id);

        self::compareDataWithModel($stage->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReportCostingStage()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $data             = new AssessmentReportCostingStageData([
            'name'     => $this->faker->word,
            'position' => $this->faker->numberBetween(1, 100),
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $section = $this->service->create($data, $assessmentReport->id);

        self::compareDataWithModel($data->toArray(), $section);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToCreateAssessmentReportCostingStageWhenAssessmentReportIsApproved()
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::CLIENT_APPROVED);
        $data             = new AssessmentReportCostingStageData([
            'name'     => $this->faker->word,
            'position' => $this->faker->numberBetween(1, 100),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data, $assessmentReport->id);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReportCostingStage()
    {
        $stage = $this->fakeAssessmentReportCostingStage();
        $data  = new AssessmentReportCostingStageData([
            'name'     => $this->faker->word,
            'position' => $this->faker->numberBetween(1, 100),
        ]);

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->service->update($data, $stage->assessment_report_id, $stage->id);

        $reloaded = AssessmentReportCostingStage::findOrFail($stage->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testFailToUpdateAssessmentReportCostingStageWhenAssessmentReportIsApproved()
    {
        $stage = $this->fakeAssessmentReportCostingStage(AssessmentReportStatuses::CLIENT_APPROVED);
        $data  = new AssessmentReportCostingStageData([
            'name'     => $this->faker->word,
            'position' => $this->faker->numberBetween(1, 100),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update($data, $stage->assessment_report_id, $stage->id);
    }

    /**
     * @throws \Exception
     */
    public function testDeleteAssessmentReportCostingStage()
    {
        $stage = $this->fakeAssessmentReportCostingStage();

        self::expectsEvents(AssessmentReportEntityUpdated::class);
        $this->service->delete($stage->assessment_report_id, $stage->id);

        self::expectException(ModelNotFoundException::class);
        AssessmentReportCostingStage::findOrFail($stage->id);
    }

    /**
     * @throws \Exception
     */
    public function testFailDeleteAssessmentReportCostingStageWhenAssessmentReportIsApproved()
    {
        $stage = $this->fakeAssessmentReportCostingStage(AssessmentReportStatuses::CLIENT_APPROVED);

        self::expectException(NotAllowedException::class);
        $this->service->delete($stage->assessment_report_id, $stage->id);
    }
}
