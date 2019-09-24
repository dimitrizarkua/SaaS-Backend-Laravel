<?php

namespace Tests\Integration\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Interfaces\JobStatusWorkflowInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Services\JobsMergeService;
use App\Components\Locations\Models\Location;
use Tests\API\ApiTestCase;

/**
 * Class JobsListingWorkflowTest
 *
 * @package Tests\Integration\Jobs
 *
 * @group   integration
 * @group   jobs
 * @group   jobs-workflow
 */
class JobsListingWorkflowTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_recurring',
        'jobs.manage_inbox',
        'jobs.create',
        'jobs.assign_staff',
        'jobs.update',
        'jobs.delete',
        'teams.create',
        'teams.modify_members',
    ];

    /** @var \App\Components\Locations\Models\Location */
    private $location;

    public function setUp()
    {
        parent::setUp();
        $this->location = factory(Location::class)->create();
        $this->user->locations()->attach($this->location);
    }

    /**
     * Issue SN-604 test
     *
     * @see https://pushstack.atlassian.net/browse/SN-604
     */
    public function testShouldBeClosed()
    {
        //1. Assert that there are no any jobs
        $info = $this->getInfo();
        self::assertEquals(0, $info['inbox']);
        self::assertEquals(0, $info['mine']);
        self::assertEquals(0, $info['active']);
        self::assertEquals(0, $info['closed']);
        self::assertEmpty($info['teams']);
        self::assertEquals(0, $info['no_contact_24_hours']);
        self::assertEquals(0, $info['upcoming_kpi']);

        //2. Create new job
        $jobId = $this->createNewJob();

        //3. Activate the job
        $this->changeJobStatus($jobId, JobStatuses::IN_PROGRESS);

        //4. Assert that there is one active job
        $info = $this->getInfo();
        self::assertEquals(1, $info['active']);
        self::assertEquals(0, $info['closed']);

        //5. Close the job
        $this->changeJobStatus($jobId, JobStatuses::CLOSED);

        //6. Assert that there is one closed job
        $info = $this->getInfo();
        self::assertEquals(0, $info['active']);
        self::assertEquals(1, $info['closed']);
    }

    /**
     * Issue SN-603 test
     *
     * @see https://pushstack.atlassian.net/browse/SN-603
     */
    public function testActiveTab()
    {
        //1. Assert that there are no any active jobs
        self::assertEquals(0, $this->getInfo()['active']);

        //2. Create one job
        $jobId = $this->createNewJob();

        //3. Move it to in progress
        $this->changeJobStatus($jobId, JobStatuses::IN_PROGRESS);

        self::assertEquals(1, $this->getInfo()['active']);
    }

    public function testTeams()
    {
        $jobId  = $this->createNewJob();
        $teamId = $this->createTeam('Dream Team');
        $this->addUserToTeam($teamId, $this->user->id);
        $this->assignJobToTeam($jobId, $teamId);

        $info = $this->getInfo();
        self::assertEquals(1, $info['teams'][0]['jobs_count']);
    }

    public function testRecalculationTeamsCounters()
    {
        $jobId  = $this->createNewJob();
        $teamId = $this->createTeam('Dream Team');
        $this->assignJobToTeam($jobId, $teamId);
        $this->addUserToTeam($teamId, $this->user->id);

        $info = $this->getInfo();
        $jobs = $this->getJobsForTeam($teamId);
        self::assertEquals(1, $info['teams'][0]['jobs_count']);
        self::assertCount(1, $jobs);

        $this->changeJobStatus($jobId, JobStatuses::CLOSED);

        $info = $this->getInfo();
        $jobs = $this->getJobsForTeam($teamId);
        self::assertNotEmpty($info['teams']);
        self::assertEmpty($jobs);
    }

    public function testDeleteJobRecalculateInboxAndActiveAndMineCounters(): void
    {
        //1. Assert that there are no active and mine jobs
        $info = $this->getInfo();
        self::assertEquals(0, $info['inbox']);
        self::assertEquals(0, $info['mine']);
        self::assertEquals(0, $info['active']);

        //2. Create new job and pin it to inbox
        $jobId = $this->createNewJob();
        $this->pinJob($jobId);

        //3. Activate the job
        $this->changeJobStatus($jobId, JobStatuses::IN_PROGRESS);

        //4. Assert that there is one inbox, active and mine job
        $info = $this->getInfo();
        self::assertEquals(1, $info['inbox']);
        self::assertEquals(1, $info['active']);
        self::assertEquals(1, $info['mine']);

        //5. Delete the job
        $this->deleteJob($jobId);

        //6. Assert that there is no job in counters
        $info = $this->getInfo();
        self::assertEquals(0, $info['inbox']);
        self::assertEquals(0, $info['active']);
        self::assertEquals(0, $info['mine']);
    }

    public function testDeleteJobRecalculateClosedCounters(): void
    {
        //1. Assert that there are no closed jobs
        $info = $this->getInfo();
        self::assertEquals(0, $info['closed']);

        //2. Create new job and set status to closed
        $jobId = $this->createNewJob();

        $job     = Job::findOrFail($jobId);
        $service = $this->app->make(JobStatusWorkflowInterface::class);
        $service->setJob($job)->changeStatus(JobStatuses::CLOSED, null, $this->user->id);

        //3. Assert that there is one closed job
        $info = $this->getInfo();
        self::assertEquals(1, $info['closed']);

        //4. Delete the job
        $this->deleteJob($jobId);

        //5. Assert that there is no closed job
        $info = $this->getInfo();
        self::assertEquals(0, $info['closed']);
    }

    public function testRecalculationTeamsCountersWhenDeleteJob(): void
    {
        //1. Create new job and team
        $jobId  = $this->createNewJob();
        $teamId = $this->createTeam('Dream Team');

        //2. Assign job and user to the team
        $this->addUserToTeam($teamId, $this->user->id);
        $this->assignJobToTeam($jobId, $teamId);

        //3. Assert that there is one job to team
        $info = $this->getInfo();
        $jobs = $this->getJobsForTeam($teamId);
        self::assertEquals(1, $info['teams'][0]['jobs_count']);
        self::assertCount(1, $jobs);

        //4. Delete the job
        $this->deleteJob($jobId);

        //5. Assert that there is no job to team
        $info = $this->getInfo();
        $jobs = $this->getJobsForTeam($teamId);
        self::assertNotEmpty($info['teams']);
        self::assertEmpty($jobs);
    }

    /**
     * @throws \Throwable
     */
    public function testMergeJobRecalculateCounters(): void
    {
        //1. Assert that there are no jobs
        $info = $this->getInfo();
        self::assertEquals(0, $info['mine']);
        self::assertEquals(0, $info['active']);
        self::assertEquals(0, $info['closed']);

        //2. Create new jobs and activate
        $firstJobId  = $this->createNewJob();
        $secondJobId = $this->createNewJob();
        $this->changeJobStatus($firstJobId, JobStatuses::IN_PROGRESS);
        $this->changeJobStatus($secondJobId, JobStatuses::IN_PROGRESS);

        //3. Assert that there are created active jobs
        $info = $this->getInfo();
        self::assertEquals(2, $info['mine']);
        self::assertEquals(2, $info['active']);
        self::assertEquals(0, $info['closed']);

        //4. Merge jobs
        $service = $this->app->make(JobsMergeService::class);
        $service->mergeJobs($firstJobId, $secondJobId, $this->user->id);

        //5. Assert that there is one mine, active and closed jobs
        $info = $this->getInfo();
        self::assertEquals(1, $info['mine']);
        self::assertEquals(1, $info['active']);
        self::assertEquals(1, $info['closed']);
    }

    private function getJobsForTeam(int $teamId)
    {
        $url = action('Jobs\JobListController@mineTeams', [
            'team' => $teamId,
        ]);

        return $this
            ->getJson($url)
            ->assertStatus(200)
            ->getData();
    }

    private function createTeam(string $name): int
    {
        $url = action('Teams\TeamsController@store');

        return $this
            ->postJson($url, [
                'name' => $name,
            ])
            ->assertStatus(201)
            ->getData('id');
    }

    private function addUserToTeam(int $teamId, int $userId): void
    {
        $url = action('Teams\TeamsController@addUser', [
            'team' => $teamId,
            'user' => $userId,
        ]);

        $this->postJson($url)->assertStatus(200);
    }

    private function assignJobToTeam(int $jobId, int $teamId): void
    {
        $url = action('Jobs\JobTeamsController@assignToTeam', [
            'job'  => $jobId,
            'team' => $teamId,
        ]);

        $this->postJson($url)->assertStatus(200);
    }

    private function getInfo()
    {
        $infoUrl = action('Jobs\JobListController@info');

        return $this->getJson($infoUrl)
            ->assertStatus(200)
            ->getData();
    }

    private function createNewJob(array $data = []): int
    {
        if (empty($data)) {
            $data = [
                'claim_number'         => $this->faker->word,
                'assigned_location_id' => $this->location->id,
            ];
        }

        $url = action('Jobs\JobsController@store');

        return $this->postJson($url, $data)
                   ->assertStatus(201)
                   ->getData()['id'];
    }

    private function deleteJob(int $jobId): void
    {
        $job = Job::findOrFail($jobId);

        $service = $this->app->make(JobsServiceInterface::class);
        $service->deleteJob($job, $this->user->id);
    }

    private function pinJob(int $jobId): void
    {
        $url = action('Jobs\JobPinsController@pinJob', [
            'job' => $jobId,
        ]);

        $this->postJson($url)
            ->assertStatus(200);
    }

    private function assignJobToUser(int $jobId, int $userId): void
    {
        $assignUrl = action('Jobs\JobUsersController@assignToUser', [
            'job'  => $jobId,
            'user' => $userId,
        ]);
        $this->postJson($assignUrl)
            ->assertStatus(200);
    }

    private function changeJobStatus(int $jobId, string $status): void
    {
        $job = Job::findOrFail($jobId);

        $service = $this->app->make(JobStatusWorkflowInterface::class);
        $service->setJob($job)->changeStatus($status, null, $this->user->id);
    }
}
