<?php

namespace Tests\API\Jobs;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Components\AssessmentReports\Events\AssessmentReportCreated;
use App\Components\AssessmentReports\Events\AssessmentReportUpdated;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Resources\FullAssessmentReportResource;
use App\Components\Jobs\Models\Job;
use App\Components\RBAC\Models\Role;
use App\Http\Responses\AssessmentReports\AssessmentReportListResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportResponse;
use App\Http\Responses\AssessmentReports\AssessmentReportStatusAndTotalResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\AssessmentReports\AssessmentReportTestCase;

/**
 * Class JobAssessmentReportsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   assessment-reports
 * @group   api
 */
class JobAssessmentReportsControllerTest extends AssessmentReportTestCase
{
    protected $permissions = [
        'assessment_reports.view',
        'assessment_reports.manage',
    ];

    public function testGetJobAssessmentReports(): void
    {
        $count = $this->faker->numberBetween(1, 5);
        /** @var Job $job */
        $job = factory(Job::class)->create();
        factory(AssessmentReport::class, $count)->create([
            'job_id' => $job->id,
        ]);

        $url = action('Jobs\JobAssessmentReportsController@index', [
            'job_id' => $job->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count)
            ->assertValidSchema(AssessmentReportListResponse::class, true);
    }

    public function testGetJobAssessmentReport(): void
    {
        /** @var AssessmentReport $model */
        $model = factory(AssessmentReport::class)->create([
            'user_id' => $this->user->id,
        ]);
        $url   = action('Jobs\JobAssessmentReportsController@show', [
            'job_id'               => $model->job_id,
            'assessment_report_id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(FullAssessmentReportResource::class);
        $data     = $response->getData();

        self::compareDataWithModel($data, $model);
        self::assertEquals($this->user->id, $model->user_id);
        self::assertArrayHasKey('job', $data);
        self::assertArrayHasKey('user', $data);
        self::assertArrayHasKey('latest_status', $data);
        self::assertArrayHasKey('sections', $data);
    }

    /**
     * @throws \Exception
     */
    public function testCreateAssessmentReport(): void
    {
        /** @var Job $job */
        $job     = factory(Job::class)->create();
        $request = [
            'heading'    => $this->faker->text(),
            'subheading' => $this->faker->text(),
            'date'       => $this->faker->date(),
        ];
        $url     = action('Jobs\JobAssessmentReportsController@store', [
            'job_id' => $job->id,
        ]);

        $this->expectsEvents(AssessmentReportCreated::class);
        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportResponse::class, true);
        $data     = $response->getData();
        $reloaded = AssessmentReport::findOrFail($data['id']);

        self::assertEquals($job->id, $reloaded->job_id);
        self::assertEquals($this->user->id, $reloaded->user_id);
        self::compareDataWithModel($request, $reloaded);
    }

    /**
     * @throws \Exception
     */
    public function testUpdateAssessmentReport(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $request          = [
            'heading'    => $this->faker->text(),
            'subheading' => $this->faker->text(),
            'date'       => $this->faker->date(),
        ];
        $url              = action('Jobs\JobAssessmentReportsController@update', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->expectsEvents(AssessmentReportUpdated::class);
        $this->patchJson($url, $request)
            ->assertStatus(200)
            ->assertSeeData();
        $reloaded = AssessmentReport::findOrFail($assessmentReport->id);

        self::compareDataWithModel($request, $reloaded);
    }

    public function testDeleteAssessmentReport(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $url              = action('Jobs\JobAssessmentReportsController@destroy', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        $this->expectException(ModelNotFoundException::class);
        AssessmentReport::findOrFail($assessmentReport->id);
    }

    public function testGetNextStatusesFromDraft(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::DRAFT);
        $url              = action('Jobs\JobAssessmentReportsController@getNextStatuses', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(AssessmentReportStatuses::PENDING_CLIENT_APPROVAL, $data);
        self::assertContains(AssessmentReportStatuses::CANCELLED, $data);
    }

    public function testGetNextStatusesFromPendingClientApproval(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::PENDING_CLIENT_APPROVAL);
        $url              = action('Jobs\JobAssessmentReportsController@getNextStatuses', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(AssessmentReportStatuses::CLIENT_APPROVED, $data);
        self::assertContains(AssessmentReportStatuses::CLIENT_CANCELLED, $data);
        self::assertContains(AssessmentReportStatuses::CANCELLED, $data);
    }

    public function testGetNextStatusesFromClientCancelled(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::CLIENT_CANCELLED);
        $url              = action('Jobs\JobAssessmentReportsController@getNextStatuses', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(AssessmentReportStatuses::DRAFT, $data);
    }

    public function testGetNextStatusesFromCancelled(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::CANCELLED);
        $url              = action('Jobs\JobAssessmentReportsController@getNextStatuses', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(AssessmentReportStatuses::DRAFT, $data);
    }

    public function testChangeStatus(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $data             = [
            'status' => AssessmentReportStatuses::PENDING_CLIENT_APPROVAL,
        ];

        $url = action('Jobs\JobAssessmentReportsController@changeStatus', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(200);

        $reloaded = AssessmentReport::find($assessmentReport->id);
        self::assertEquals($reloaded->latestStatus->status, $data['status']);
    }

    public function testChangeStatusRequiresAdditionalApprovePermission(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::PENDING_CLIENT_APPROVAL);
        $data             = [
            'status' => AssessmentReportStatuses::CLIENT_APPROVED,
        ];

        $url = action('Jobs\JobAssessmentReportsController@changeStatus', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(403);

        /** @var $role */
        $role = $this->user->roles()->first();
        $role->permissions()->create([
            'permission' => 'assessment_reports.approve',
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(200);

        $reloaded = AssessmentReport::find($assessmentReport->id);
        self::assertEquals($reloaded->latestStatus->status, $data['status']);
    }

    public function testChangeStatusRequiresAdditionalManageCancelledPermission(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::CLIENT_CANCELLED);
        $data             = [
            'status' => AssessmentReportStatuses::DRAFT,
        ];

        $url = action('Jobs\JobAssessmentReportsController@changeStatus', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(403);

        /** @var Role $role */
        $role = $this->user->roles()->first();
        $role->permissions()->create([
            'permission' => 'assessment_reports.manage_cancelled',
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(200);

        $reloaded = AssessmentReport::find($assessmentReport->id);
        self::assertEquals($reloaded->latestStatus->status, $data['status']);
    }

    public function testFailToChangeStatusWhenInvalidStatusName(): void
    {
        $assessmentReport = $this->fakeAssessmentReportWithStatus(AssessmentReportStatuses::DRAFT);
        $data             = [
            'status' => $this->faker->word,
        ];

        $url = action('Jobs\JobAssessmentReportsController@changeStatus', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(422);
    }

    public function testFailToChangeStatusWhenNewStatusIsNotInTransition(): void
    {
        // default status is DRAFT
        $assessmentReport = $this->fakeAssessmentReportWithStatus();
        $data             = [
            'status' => AssessmentReportStatuses::DRAFT,
        ];

        $url = action('Jobs\JobAssessmentReportsController@changeStatus', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(405);
    }

    public function testGetJobAssessmentReportStatusAndTotal(): void
    {
        $status           = $this->faker->randomElement(AssessmentReportStatuses::values());
        $assessmentReport = $this->fakeAssessmentReportWithStatus($status);
        factory(AssessmentReportCostItem::class)->create([
            'assessment_report_id' => $assessmentReport->id,
        ]);
        $url = action('Jobs\JobAssessmentReportsController@getStatusAndTotal', [
            'job_id'               => $assessmentReport->job_id,
            'assessment_report_id' => $assessmentReport->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertValidSchema(AssessmentReportStatusAndTotalResponse::class, true);
        $data     = $response->getData();

        self::assertEquals($data['total'], $assessmentReport->getTotalAmount());
        self::assertEquals($data['status'], $status);
    }
}
