<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTeam;
use App\Components\Teams\Models\Team;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class JobTeamsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobTeamsControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.assign_staff',
    ];

    public function testListAssignedTeams()
    {
        $job = $this->fakeJobWithStatus();

        $count = $this->faker->numberBetween(1, 5);
        factory(JobTeam::class, $count)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobTeamsController@listAssignedTeams', ['job_id' => $job->id,]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    public function testAssignTeamToJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Team $team */
        $team = factory(Team::class)->create();

        $url = action('Jobs\JobTeamsController@assignToTeam', [
            'job_id'  => $job->id,
            'team_id' => $team->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        JobTeam::query()->where([
            'job_id'  => $job->id,
            'team_id' => $team->id,
        ])->firstOrFail();
    }

    public function testFailAssignTeamToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var Team $team */
        $team = factory(Team::class)->create();

        $url = action('Jobs\JobTeamsController@assignToTeam', [
            'job_id'  => $job->id,
            'team_id' => $team->id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testNotAllowedResponseWhenAlreadyAssigned()
    {
        $job = $this->fakeJobWithStatus();

        /** @var JobTeam $jobTeam */
        $jobTeam = factory(JobTeam::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobTeamsController@assignToTeam', [
            'job_id'  => $job->id,
            'team_id' => $jobTeam->team_id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    public function testUnassignTeamFromJob()
    {
        /** @var JobTeam $jobTeam */
        $jobTeam = factory(JobTeam::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $url = action('Jobs\JobTeamsController@unassignFromTeam', [
            'job_id'  => $jobTeam->job_id,
            'team_id' => $jobTeam->team_id,
        ]);
        $this->deleteJson($url)->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        JobTeam::query()->where([
            'job_id'  => $jobTeam->job_id,
            'team_id' => $jobTeam->team_id,
        ])->firstOrFail();
    }

    public function testFailUnassignTeamFromJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var JobTeam $jobTeam */
        $jobTeam = factory(JobTeam::class)->create([
            'job_id' => $job->id,
        ]);

        $url = action('Jobs\JobTeamsController@unassignFromTeam', [
            'job_id'  => $jobTeam->job_id,
            'team_id' => $jobTeam->team_id,
        ]);
        $this->deleteJson($url)->assertStatus(405);
    }
}
