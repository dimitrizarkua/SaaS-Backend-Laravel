<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\JobFollower;
use App\Components\Jobs\Models\JobUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class JobFollowersControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobFollowersControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
    ];

    public function testFollowJob()
    {
        $job = $this->fakeJobWithStatus();

        $url = action('Jobs\JobFollowersController@followJob', ['job_id' => $job->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(200);

        JobFollower::query()->where([
            'job_id'  => $job->id,
            'user_id' => $this->user->id,
        ])->firstOrFail();
    }

    public function testFailFollowClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        $url = action('Jobs\JobFollowersController@followJob', ['job_id' => $job->id]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testNotAllowedResponseWhenAlreadyFollows()
    {
        $job = $this->fakeJobWithStatus();

        factory(JobFollower::class)->create([
            'job_id'  => $job->id,
            'user_id' => $this->user->id,
        ]);

        $url = action('Jobs\JobFollowersController@followJob', ['job_id' => $job->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(405);
    }

    public function testUnassignUserFromJob()
    {
        /** @var JobUser $jobUser */
        $jobUser = factory(JobUser::class)->create([
            'job_id'  => $this->fakeJobWithStatus()->id,
            'user_id' => $this->user->id,
        ]);

        $url = action('Jobs\JobFollowersController@unfollowJob', [
            'job_id'  => $jobUser->job_id,
            'user_id' => $jobUser->user_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        JobFollower::query()->where([
            'job_id'  => $jobUser->job_id,
            'user_id' => $jobUser->user_id,
        ])->firstOrFail();
    }

    public function testFailUnfollowClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var JobUser $jobUser */
        $jobUser = factory(JobUser::class)->create([
            'job_id'  => $job->id,
            'user_id' => $this->user->id,
        ]);

        $url = action('Jobs\JobFollowersController@unfollowJob', [
            'job_id'  => $jobUser->job_id,
            'user_id' => $jobUser->user_id,
        ]);
        $this->deleteJson($url)->assertStatus(405);
    }
}
