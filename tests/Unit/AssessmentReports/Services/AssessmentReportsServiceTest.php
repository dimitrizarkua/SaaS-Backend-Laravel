<?php

namespace Tests\Unit\AssessmentReports\Services;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportCreated;
use App\Components\AssessmentReports\Events\AssessmentReportUpdated;
use App\Components\AssessmentReports\Exceptions\NotAllowedException;
use App\Components\AssessmentReports\Interfaces\AssessmentReportsServiceInterface;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportStatus;
use App\Components\AssessmentReports\Models\VO\AssessmentReportData;
use App\Components\Jobs\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Tests\Unit\AssessmentReports\AssessmentReportFaker;

/**
 * Class AssessmentReportsServiceTest
 *
 * @package Tests\Unit\AssessmentReports\Services
 * @group   assessment-reports
 * @group   services
 */
class AssessmentReportsServiceTest extends TestCase
{
    use AssessmentReportFaker;

    /**
     * @var AssessmentReportsServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(AssessmentReportsServiceInterface::class);
    }

    public function testGetAssessmentReport(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();

        $reloaded = $this->service->getAssessmentReport($assessmentReport->id);

        self::compareDataWithModel($assessmentReport->toArray(), $reloaded);
    }

    public function testGetFullAssessmentReport(): void
    {
        /** @var AssessmentReport $assessmentReport */
        $assessmentReport = factory(AssessmentReport::class)->create();

        $reloaded = $this->service->getFullAssessmentReport($assessmentReport->id);

        self::compareDataWithModel($assessmentReport->toArray(), $reloaded);
        $reloaded = $reloaded->toArray();
        self::assertArrayHasKey('job', $reloaded);
        self::assertArrayHasKey('user', $reloaded);
        self::assertArrayHasKey('latest_status', $reloaded);
        self::assertArrayHasKey('sections', $reloaded);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testCreateAssessmentReport(): void
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Job $job */
        $job  = factory(Job::class)->create();
        $data = new AssessmentReportData([
            'heading'    => $this->faker->text(),
            'subheading' => $this->faker->text(),
            'date'       => $this->faker->date(),
        ]);

        $this->expectsEvents(AssessmentReportCreated::class);
        $assessmentReport = $this->service->createAssessmentReport($data, $job->id, $user->id);
        /** @var AssessmentReportStatus $status */
        $status = $assessmentReport->latestStatus()->first();

        self::assertEquals($job->id, $assessmentReport->job_id);
        self::assertEquals($user->id, $assessmentReport->user_id);
        self::compareDataWithModel($data->toArray(), $assessmentReport);
        self::assertNotNull($status);
        self::assertEquals($status->user_id, $user->id);
        self::assertEquals($status->status, AssessmentReportStatuses::DRAFT);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Exception
     */
    public function testUpdateAssessmentReport(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $data             = new AssessmentReportData([
            'heading'    => $this->faker->text(),
            'subheading' => $this->faker->text(),
            'date'       => $this->faker->date(),
        ]);

        $this->expectsEvents(AssessmentReportUpdated::class);
        $this->service->updateAssessmentReport($data, $assessmentReport->id);
        $reloaded = AssessmentReport::findOrFail($assessmentReport->id);

        self::compareDataWithModel($data->toArray(), $reloaded);
    }

    public function testDeleteAssessmentReport(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();

        $this->service->deleteAssessmentReport($assessmentReport->id);

        $this->expectException(ModelNotFoundException::class);
        AssessmentReport::findOrFail($assessmentReport->id);
    }

    public function testThrowExceptionIfAssessmentReportIsApproved(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::CLIENT_APPROVED);

        $this->expectException(NotAllowedException::class);
        $this->service->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReport->id);
    }

    public function testThrowExceptionIfAssessmentReportIsCancelled(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(
            $this->faker->randomElement(AssessmentReportStatuses::$cancelledStatuses)
        );

        $this->expectException(NotAllowedException::class);
        $this->service->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReport->id);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testExceptionDoesNotThrowWhenAssessmentReportIsDraftOrPendingApproval(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(
            $this->faker->randomElement([
                AssessmentReportStatuses::DRAFT,
                AssessmentReportStatuses::PENDING_CLIENT_APPROVAL,
            ])
        );

        $this->service->throwExceptionIfAssessmentReportIsApprovedOrCancelled($assessmentReport->id);
    }

    public function testGetJobAssessmentReportStatusAndTotal(): void
    {
        $status           = $this->faker->randomElement(AssessmentReportStatuses::values());
        $assessmentReport = $this->fakeAssessmentReportWithStatus($status);
        factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $data = $this->service->getStatusAndTotal($assessmentReport->id);

        self::assertEquals($data['total'], $assessmentReport->getTotalAmount());
        self::assertEquals($data['status'], $status);
    }
}
