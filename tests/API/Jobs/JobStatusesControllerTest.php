<?php

namespace Tests\API\Jobs;

use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobUser;

/**
 * Class JobStatusesControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 */
class JobStatusesControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.update',
    ];

    public function testNextStatusesFromNew()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::NEW);
        $url = action('Jobs\JobStatusesController@listNextStatuses', ['job_id' => $job->id]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(JobStatuses::ON_HOLD, $data);
        self::assertContains(JobStatuses::IN_PROGRESS, $data);
        self::assertContains(JobStatuses::CANCELLED, $data);
        self::assertContains(JobStatuses::CLOSED, $data);
    }

    public function testNextStatusesFromOnHold()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::ON_HOLD);
        $url = action('Jobs\JobStatusesController@listNextStatuses', ['job_id' => $job->id]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(JobStatuses::NEW, $data);
        self::assertContains(JobStatuses::IN_PROGRESS, $data);
        self::assertContains(JobStatuses::CANCELLED, $data);
        self::assertContains(JobStatuses::CLOSED, $data);
    }

    public function testNextStatusesFromInProgress()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::IN_PROGRESS);
        $url = action('Jobs\JobStatusesController@listNextStatuses', ['job_id' => $job->id]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(JobStatuses::CANCELLED, $data);
        self::assertContains(JobStatuses::CLOSED, $data);
    }

    public function testNextStatusesFromClosed()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::CLOSED);
        $url = action('Jobs\JobStatusesController@listNextStatuses', ['job_id' => $job->id]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(JobStatuses::NEW, $data);
        self::assertContains(JobStatuses::IN_PROGRESS, $data);
        self::assertContains(JobStatuses::ON_HOLD, $data);
    }

    public function testNextStatusesFromCancelled()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::CANCELLED);
        $url = action('Jobs\JobStatusesController@listNextStatuses', ['job_id' => $job->id]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertContains(JobStatuses::NEW, $data);
        self::assertContains(JobStatuses::IN_PROGRESS, $data);
        self::assertContains(JobStatuses::ON_HOLD, $data);
    }

    public function testStatusChangeSuccess()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::NEW);
        factory(JobUser::class)->create([
            'job_id'  => $job->id,
            'user_id' => $this->user->id,
        ]);
        $data = [
            'status' => JobStatuses::IN_PROGRESS,
            'note'   => $this->faker->word,
        ];

        $url = action('Jobs\JobStatusesController@changeStatus', ['job_id' => $job->id]);

        $this->patchJson($url, $data)->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));
        self::assertEquals($reloaded->getCurrentStatus(), $data['status']);
        self::assertEquals($reloaded->latestStatus->note, $data['note']);
    }

    public function testStatusChangeValidationFail()
    {
        $job  = $this->fakeJobWithStatus(JobStatuses::NEW);
        $data = [
            'status' => $this->faker->word,
        ];

        $url = action('Jobs\JobStatusesController@changeStatus', ['job_id' => $job->id]);
        $this->patchJson($url, $data)->assertStatus(422);
    }

    public function testStatusChangeInvalidStatusFail()
    {
        $job  = $this->fakeJobWithStatus(JobStatuses::NEW);
        $data = [
            'status' => JobStatuses::NEW,
        ];

        $url = action('Jobs\JobStatusesController@changeStatus', ['job_id' => $job->id]);
        $this->patchJson($url, $data)->assertStatus(405);
    }

    public function testFailCloseJobWithUnpaidInvoice()
    {
        $job = $this->fakeJobWithStatus(JobStatuses::IN_PROGRESS);

        /** @var Invoice $invoice */
        $invoice = factory(Invoice::class)->create([
            'job_id' => $job->id,
        ]);
        factory(InvoiceItem::class)->create([
            'invoice_id' => $invoice->id,
        ]);

        $data = [
            'status' => JobStatuses::CLOSED,
        ];

        $url = action('Jobs\JobStatusesController@changeStatus', ['job_id' => $job->id]);
        $this->patchJson($url, $data)
            ->assertNotAllowed();
    }
}
