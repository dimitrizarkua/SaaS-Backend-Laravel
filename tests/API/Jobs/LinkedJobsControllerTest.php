<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\LinkedJob;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class LinkedJobsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class LinkedJobsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_jobs',
    ];

    public function testLinkJobs()
    {
        /** @var Job $job */
        $job  = factory(Job::class)->create();
        $linkedJob = factory(Job::class)->create();

        $url = action('Jobs\LinkedJobsController@linkJobs', [
            'job_id'        => $job->id,
            'linked_job_id' => $linkedJob->id,
        ]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(200);

        LinkedJob::query()
            ->where([
                'job_id'        => $job->id,
                'linked_job_id' => $linkedJob->id,
            ])
            ->firstOrFail();

        LinkedJob::query()
            ->where([
                'linked_job_id' => $job->id,
                'job_id'        => $linkedJob->id,
            ])
            ->firstOrFail();
    }

    public function testFailToDuplicateLinkJobs()
    {
        $linkedJob = factory(LinkedJob::class)->create();

        $url = action('Jobs\LinkedJobsController@linkJobs', [
            'job_id'        => $linkedJob->job_id,
            'linked_job_id' => $linkedJob->job_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(405);
    }

    public function testFailToReverseLinkJobs()
    {
        $linkedJob = factory(LinkedJob::class)->create();

        $url = action('Jobs\LinkedJobsController@linkJobs', [
            'job_id'        => $linkedJob->linked_job_id,
            'linked_job_id' => $linkedJob->job_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(405);
    }

    public function testUnlinkJobs()
    {
        $linkedJob = factory(LinkedJob::class)->create();

        $url = action('Jobs\LinkedJobsController@unlinkJobs', [
            'job_id'        => $linkedJob->job_id,
            'linked_job_id' => $linkedJob->linked_job_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        Job::findOrFail($linkedJob->job_id);
        Job::findOrFail($linkedJob->linked_job_id);

        self::expectException(ModelNotFoundException::class);
        LinkedJob::query()
            ->where([
                'linked_job_id' => $linkedJob->job_id,
                'job_id'        => $linkedJob->linked_job_id,
            ])
            ->firstOrFail();
    }

    public function testUnlinkJobsSecondLinkRemoved()
    {
        $linkedJob = factory(LinkedJob::class)->create();

        $url = action('Jobs\LinkedJobsController@unlinkJobs', [
            'job_id'        => $linkedJob->job_id,
            'linked_job_id' => $linkedJob->linked_job_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);
        LinkedJob::query()
            ->where([
                'linked_job_id' => $linkedJob->job_id,
                'job_id'        => $linkedJob->linked_job_id,
            ])
            ->firstOrFail();
    }

    public function testUnlinkReverseJobs()
    {
        $linkedJob = factory(LinkedJob::class)->create();

        $url = action('Jobs\LinkedJobsController@unlinkJobs', [
            'job_id'        => $linkedJob->linked_job_id,
            'linked_job_id' => $linkedJob->job_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        Job::findOrFail($linkedJob->job_id);
        Job::findOrFail($linkedJob->linked_job_id);
    }

    public function testListLinkedJobs()
    {
        $job = factory(Job::class)->create();

        $count = $this->faker->numberBetween(1, 3);
        factory(LinkedJob::class, $count)->create(['job_id' => $job->id]);

        $url      = action('Jobs\LinkedJobsController@listLinkedJobs', ['job_id' => $job->id,]);
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }
}
