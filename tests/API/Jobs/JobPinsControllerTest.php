<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\Job;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobPinsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobPinsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.manage_inbox',
    ];

    public function testPinJob()
    {
        /** @var Job $job */
        $job = factory(Job::class)->create(['pinned_at' => null]);

        $url = action('Jobs\JobPinsController@pinJob', ['job_id' => $job->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(200);

        $job = Job::findOrFail($job->id);

        self::assertNotNull($job->pinned_at);
    }

    public function testUnpinJob()
    {
        /** @var Job $job */
        $job = factory(Job::class)->create(['pinned_at' => Carbon::yesterday()]);

        $url = action('Jobs\JobPinsController@unpinJob', ['job_id' => $job->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        $job = Job::findOrFail($job->id);

        self::assertNull($job->pinned_at);
    }
}
