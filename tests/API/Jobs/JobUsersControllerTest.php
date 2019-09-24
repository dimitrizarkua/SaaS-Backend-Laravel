<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobUser;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class JobUsersControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobUsersControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.assign_staff',
    ];

    public function testListAssignedUsers()
    {
        $job = $this->fakeJobWithStatus();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobUser::class, $count)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobUsersController@listAssignedUsers', ['job_id' => $job->id,]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testAssignUserToJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var User $user */
        $user = factory(User::class)->create();

        $url = action('Jobs\JobUsersController@assignToUser', [
            'job_id'  => $job->id,
            'user_id' => $user->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        JobUser::query()->where([
            'job_id'  => $job->id,
            'user_id' => $user->id,
        ])->firstOrFail();
    }

    public function testFailAssignUserToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var User $user */
        $user = factory(User::class)->create();

        $url = action('Jobs\JobUsersController@assignToUser', [
            'job_id'  => $job->id,
            'user_id' => $user->id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testNotAllowedResponseWhenAlreadyAssigned()
    {
        $job = $this->fakeJobWithStatus();

        /** @var JobUser $jobUser */
        $jobUser = factory(JobUser::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobUsersController@assignToUser', [
            'job_id'  => $job->id,
            'user_id' => $jobUser->user_id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignUserFromJob()
    {
        $jobUser = factory(JobUser::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $url = action('Jobs\JobUsersController@unassignFromUser', [
            'job_id'  => $jobUser->job_id,
            'user_id' => $jobUser->user_id,
        ]);
        $this->deleteJson($url)->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        JobUser::query()->where([
            'job_id'  => $jobUser->job_id,
            'user_id' => $jobUser->user_id,
        ])->firstOrFail();
    }

    public function testFailUnassignUserFromClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobUser = factory(JobUser::class)->create([
            'job_id' => $job->id,
        ]);

        $url = action('Jobs\JobUsersController@unassignFromUser', [
            'job_id'  => $jobUser->job_id,
            'user_id' => $jobUser->user_id,
        ]);
        $this->deleteJson($url)->assertStatus(405);
    }
}
