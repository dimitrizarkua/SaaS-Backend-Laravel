<?php

namespace Tests\Unit\Jobs;

use App\Components\Jobs\Enums\JobCountersCacheKeys;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Enums\JobTaskTypes;
use App\Components\Jobs\Interfaces\JobCountersInterface;
use App\Components\Jobs\Interfaces\JobListingServiceInterface;
use App\Components\Jobs\Interfaces\TeamWithJobsCounterInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationUser;
use App\Components\Teams\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Class JobListingServiceTest
 *
 * @package Tests\Unit\Jobs
 * @group   jobs
 */
class JobListingServiceTest extends TestCase
{
    /**
     * @var JobListingServiceInterface
     */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->app->make(JobListingServiceInterface::class);
    }

    public function testPinnedJobsShouldBeInInbox()
    {
        //Create pinned jobs with active statuses
        $countOfPinned = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfPinned, true, true, ['pinned_at' => new Carbon]);

        //Create not pinned active jobs
        $countOfNonPinned = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfNonPinned, true, true);

        $result = $this->service->getInbox();
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfPinned, $result);
    }

    public function testNotAssignedJobsShouldBeInInbox()
    {
        //Create not assigned active jobs
        $countOfNotAssigned = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfNotAssigned, true, false);

        //Created assigned active jobs
        $countOfAssigned = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssigned, true, true);

        $result = $this->service->getInbox();
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfNotAssigned, $result);
    }

    public function testMineMethodShouldReturnJobsAssignedToUser()
    {
        $user = factory(User::class)->create();

        $countOfAssignedJobs = $this->faker->numberBetween(1, 5);

        //Create jobs assigned to user
        JobsTestFactory::createJobs($countOfAssignedJobs, true, false)
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        //Create jobs assigned to another user
        $countOfAssignedToAnotherUser = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedToAnotherUser, true, true);

        $result = $this->service->getMine($user->id);
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfAssignedJobs, $result);
    }

    public function testMineClosedShouldReturnClosedJobs()
    {
        $user     = factory(User::class)->create();
        $location = factory(Location::class)->create();
        factory(LocationUser::class)->create([
            'user_id'     => $user->id,
            'location_id' => $location->id,
        ]);

        //Create jobs assigned to user
        $countOfAssignedJobs = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedJobs, false, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        //Create closed jobs (assigned to another user)
        $countOfAssignedToAnotherUser = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedToAnotherUser, false, true);

        //Create jobs assigned to another user
        $countOfAssignedToAnotherUser = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedToAnotherUser, true, true);

        $result = $this->service->getClosed($user->id);
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfAssignedJobs, $result);
    }

    public function testMineActiveShouldReturnActiveJobs()
    {
        $user     = factory(User::class)->create();
        $location = factory(Location::class)->create();
        factory(LocationUser::class)->create([
            'user_id'     => $user->id,
            'location_id' => $location->id,
        ]);

        //Create jobs assigned to user
        $countOfAssignedJobs = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedJobs, true, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        //Create active jobs (assigned to another user)
        $countOfAssignedToAnotherUser = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedToAnotherUser, true, true);

        //Create closed jobs assigned to another user
        $countOfAssignedToAnotherUser = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfAssignedToAnotherUser, false, true);

        $result = $this->service->getActive($user->id);
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfAssignedJobs, $result);
    }

    public function testGetByTeamShouldReturnJobsAssignedToTeamWhereUserIsMemberOf()
    {
        /** @var User $user */
        $user = factory(User::class)->create();
        /** @var Team $teamOne */
        $teamOne = factory(Team::class)->create();
        /** @var Team $teamTwo */
        $teamTwo = factory(Team::class)->create();

        //Add user to both teams
        $teamOne->users()->attach($user);
        $teamTwo->users()->attach($user);

        //Create jobs assigned to both teams
        $countOfJobsAssignedToTeamOne = $this->faker->numberBetween(1, 5);
        $countOfJobsAssignedToTeamTwo = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfJobsAssignedToTeamOne, true, false)
            ->map(function (Job $job) use ($teamOne) {
                JobsTestFactory::assignJobToTeam($job, $teamOne);
            });
        JobsTestFactory::createJobs($countOfJobsAssignedToTeamTwo, true, false)
            ->map(function (Job $job) use ($teamTwo) {
                JobsTestFactory::assignJobToTeam($job, $teamTwo);
            });

        $result = $this->service->getByTeam($teamOne->id);
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfJobsAssignedToTeamOne, $result);

        $result = $this->service->getByTeam($teamTwo->id);
        self::assertInstanceOf(Collection::class, $result);
        self::assertCount($countOfJobsAssignedToTeamTwo, $result);
    }

    public function testMethodInfoShouldReturnCorrectData()
    {
        $user     = factory(User::class)->create();
        $location = factory(Location::class)->create();
        factory(LocationUser::class)->create([
            'user_id'     => $user->id,
            'location_id' => $location->id,
        ]);

        //Create jobs for inbox
        $countOfInbox = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfInbox, true, true, [
            'pinned_at'            => new Carbon,
            'assigned_location_id' => $location->id,
        ]);

        //Create mine closed jobs
        $countOfMineClosed = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfMineClosed, false, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        //Create mine active jobs
        $countOfMineActive = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfMineActive, true, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        //Create job assigned to teams
        /** @var Team $teamOne */
        $teamOne = factory(Team::class)->create();
        /** @var Team $teamTwo */
        $teamTwo = factory(Team::class)->create();

        //Add user to both teams
        $teamOne->users()->attach($user);
        $teamTwo->users()->attach($user);

        $countOfJobsAssignedToTeamOne = $this->faker->numberBetween(1, 5);
        $countOfJobsAssignedToTeamTwo = $this->faker->numberBetween(1, 5);
        JobsTestFactory::createJobs($countOfJobsAssignedToTeamOne, true, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($teamOne) {
                JobsTestFactory::assignJobToTeam($job, $teamOne);
            });
        JobsTestFactory::createJobs($countOfJobsAssignedToTeamTwo, true, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($teamTwo) {
                JobsTestFactory::assignJobToTeam($job, $teamTwo);
            });

        $countOfActive = $countOfMineActive + $countOfJobsAssignedToTeamOne + $countOfJobsAssignedToTeamTwo;
        $countOfActive += $countOfInbox;

        $info = $this->service->getCountersAndTeams($user->id);
        self::assertInstanceOf(JobCountersInterface::class, $info);
        self::assertEquals($countOfActive, $info->getAllActiveJobsCount());
        self::assertEquals($countOfMineClosed, $info->getClosedCount());
        self::assertEquals($countOfInbox, $info->getInboxCount());
        self::assertInstanceOf(Collection::class, $info->getTeams());

        /** @var TeamWithJobsCounterInterface $teamOneCounter */
        $teamOneCounter = $info->getTeams()->first(function (TeamWithJobsCounterInterface $team) use ($teamOne) {
            return $team->getTeamId() === $teamOne->id;
        });
        self::assertNotNull($teamOneCounter);
        self::assertInstanceOf(TeamWithJobsCounterInterface::class, $teamOneCounter);
        self::assertEquals($teamOne->id, $teamOneCounter->getTeamId());
        self::assertEquals($teamOne->name, $teamOneCounter->getTeamName());
        self::assertEquals($countOfJobsAssignedToTeamOne, $teamOneCounter->getJobsCount());

        $teamTwoCounter = $info->getTeams()->first(function (TeamWithJobsCounterInterface $team) use ($teamTwo) {
            return $team->getTeamId() === $teamTwo->id;
        });
        self::assertNotNull($teamTwoCounter);
        self::assertInstanceOf(TeamWithJobsCounterInterface::class, $teamTwoCounter);
        self::assertEquals($teamTwo->id, $teamTwoCounter->getTeamId());
        self::assertEquals($teamTwo->name, $teamTwoCounter->getTeamName());
        self::assertEquals($countOfJobsAssignedToTeamTwo, $teamTwoCounter->getJobsCount());
    }

    /**
     * Check data before recalculation and after.
     */
    public function testRecalculateCounters()
    {
        $cache    = taggedCache(JobCountersCacheKeys::TAG_KEY);
        $user     = factory(User::class)->create();
        $location = factory(Location::class)->create();
        factory(LocationUser::class)->create([
            'user_id'     => $user->id,
            'location_id' => $location->id,
        ]);

        $this->service->recalculateUsersCounters([$user->id]);

        $inboxBefore = $cache->get(JobCountersCacheKeys::INBOX_KEY);
        $mineBefore  = json_decode(
            $cache->get(
                sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $user->id)
            ),
            true
        );

        $countOfInbox = $this->faker->numberBetween(1, 3);
        JobsTestFactory::createJobs($countOfInbox, true, true, [
            'pinned_at'            => new Carbon,
            'assigned_location_id' => $location->id,
        ]);

        $countOfMineClosed = $this->faker->numberBetween(1, 3);
        JobsTestFactory::createJobs($countOfMineClosed, false, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        $countOfMineActive = $this->faker->numberBetween(1, 3);
        JobsTestFactory::createJobs($countOfMineActive, true, false, [
            'assigned_location_id' => $location->id,
        ])
            ->map(function (Job $job) use ($user) {
                JobsTestFactory::assignJobToUser($job, $user);
            });

        $this->service->recalculateInboxCounter();
        $this->service->recalculateUsersCounters([$user->id]);

        self::assertEquals(0, $inboxBefore);
        self::assertEquals(0, $mineBefore['active']);
        self::assertEquals(0, $mineBefore['closed']);

        self::assertEquals($countOfInbox, $cache->get(JobCountersCacheKeys::INBOX_KEY));
        $mine = json_decode(
            $cache->get(
                sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $user->id)
            ),
            true
        );

        self::assertEquals($countOfMineActive + $countOfInbox, $mine['active']);
        self::assertEquals($countOfMineClosed, $mine['closed']);

        self::assertGreaterThan($inboxBefore, $countOfInbox);
        self::assertGreaterThan($mineBefore['active'], $countOfMineActive);
        self::assertGreaterThan($mineBefore['closed'], $countOfMineClosed);
    }

    /**
     * @see https://pushstack.atlassian.net/browse/SN-327
     */
    public function testJobsInInboxShouldBeInRightOrder()
    {
        $pinnedAt = new Carbon(); //first two jobs should be pinned at the same time
        $jobOne   = JobsTestFactory::createJobs(1, true, false, [
            'pinned_at'  => $pinnedAt,
            'touched_at' => new Carbon(),
        ])->first();
        $jobTwo   = JobsTestFactory::createJobs(1, true, false, [
            'pinned_at'  => $pinnedAt,
            'touched_at' => (new Carbon())->subDays(1),
        ])->first();
        $jobThree = JobsTestFactory::createJobs(1, true, false, [
            'pinned_at'  => null,
            'touched_at' => (new Carbon())->subDays(2),
        ])->first();
        $jobFour  = JobsTestFactory::createJobs(1, true, false, [
            'pinned_at'  => null,
            'touched_at' => (new Carbon())->subDays(3),
        ])->first();

        /** @var Job[]|Collection $result */
        $result = $this->service->getInbox();
        self::assertCount(4, $result);
        self::assertEquals($jobOne->id, $result[0]->id);
        self::assertEquals($jobTwo->id, $result[1]->id);
        self::assertEquals($jobThree->id, $result[2]->id);
        self::assertEquals($jobFour->id, $result[3]->id);
    }

    public function testJobsNoContact24List()
    {
        /** @var User $user */
        $user     = factory(User::class)->create();
        $location = factory(Location::class)->create();
        $user->locations()->attach($location);

        /** @var JobTaskType $jobTaskType */
        $jobTaskType = factory(JobTaskType::class)->create([
            'name'                     => JobTaskTypes::INITIAL_CONTACT_KPI,
            'can_be_scheduled'         => false,
            'allow_edit_due_date'      => true,
            'default_duration_minutes' => 0,
            'kpi_hours'                => 24,
            'kpi_include_afterhours'   => false,
            'auto_create'              => true,
        ]);

        $jobsWithoutTasksCount = $this->faker->numberBetween(1, 3);
        $jobsWithTasksCount    = $this->faker->numberBetween(1, 3);

        factory(Job::class, $jobsWithoutTasksCount)->create([
            'assigned_location_id' => $location->id,
        ]);
        factory(Job::class, $jobsWithTasksCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) use ($jobTaskType) {
                factory(JobTask::class)->create([
                    'job_id'           => $job->id,
                    'job_task_type_id' => $jobTaskType,
                    'name'             => JobTaskTypes::INITIAL_CONTACT_KPI,
                    'kpi_missed_at'    => Carbon::yesterday(),
                ]);
            });

        $counter = $this->service->getCountersAndTeams($user->id);
        self::assertEquals($jobsWithTasksCount, $counter->getNoContact24HoursCount());
    }

    public function testUpcomingKpiCounter()
    {
        /** @var User $user */
        $user     = factory(User::class)->create();
        $location = factory(Location::class)->create();
        $user->locations()->attach($location);

        $jobsWithoutKpiCount  = $this->faker->numberBetween(1, 3);
        $jobsMissedKpiCount   = $this->faker->numberBetween(1, 3);
        $jobsUpcomingKpiCount = $this->faker->numberBetween(1, 3);

        //Jobs without KPI
        factory(Job::class, $jobsWithoutKpiCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'created_at'    => Carbon::yesterday(),
                    'kpi_missed_at' => null,
                ]);
            });
        //Jobs with missed KPI
        factory(Job::class, $jobsMissedKpiCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'created_at'    => Carbon::yesterday(),
                    'kpi_missed_at' => Carbon::now()->subHour(),
                ]);
            });
        //Jobs with upcoming KPI
        factory(Job::class, $jobsUpcomingKpiCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'created_at'    => Carbon::now()->subHour(),
                    'kpi_missed_at' => Carbon::tomorrow(),
                ]);
            });

        $counter = $this->service->getCountersAndTeams($user->id);
        self::assertEquals($jobsUpcomingKpiCount, $counter->getUpcomingKPICount());
    }

    /**
     * @see https://pushstack.atlassian.net/browse/SN-755
     *
     * @throws \Throwable
     */
    public function testClosedJobsShouldNotBeInInbox()
    {
        $countOfJobs = $this->faker->numberBetween(2, 5);
        $jobs        = factory(Job::class, $countOfJobs)->create([
            'created_at' => Carbon::now()->subMinutes(5),
        ]);

        /** @var Job $closedJob */
        $closedJob = $jobs->random();
        $closedJob->changeStatus($this->faker->randomElement(JobStatuses::$closedStatuses));

        $result = $this->service->getInbox();
        self::assertCount($countOfJobs - 1, $result);
    }
}
