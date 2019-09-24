<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Enums\JobTaskTypes;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Jobs\Models\RecurringJob;
use App\Components\Locations\Models\Location;
use App\Components\Teams\Models\Team;
use App\Http\Responses\Jobs\JobListResponse;
use App\Http\Responses\Jobs\JobsInfoResponse;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobListControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 */
class JobListControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_recurring',
    ];

    public function testListPrevious()
    {
        $recurringJobId = factory(RecurringJob::class)->create()->id;

        $countOfRecords = $this->faker->numberBetween(1, 3);

        $jobs = factory(Job::class, $countOfRecords)->create([
            'recurring_job_id' => $recurringJobId,
        ]);

        $url = action('Jobs\JobListController@listPrevious', [
            'job' => $jobs->first()->id,
        ]);

        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testInfo()
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $url = action('Jobs\JobListController@info');
        $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobsInfoResponse::class, true);
    }

    public function testNoContact24Hours()
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

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

        $jobsWithTasksCount = $this->faker->numberBetween(1, 3);
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

        $url      = action('Jobs\JobListController@noContact24Hours');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true);

        self::assertCount($jobsWithTasksCount, $response->getData());
    }

    public function testUpcompingKPI()
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $jobsWithoutKpiCount  = $this->faker->numberBetween(1, 3);
        $jobsMissedKpiCount   = $this->faker->numberBetween(1, 3);
        $jobsUpcomingKpiCount = $this->faker->numberBetween(1, 3);
        $jobsClosedCount      = $this->faker->numberBetween(1, 3);

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
        //Jobs with upcoming KPI but closed or cancelled status
        factory(Job::class, $jobsClosedCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                /** @var JobTask $jobTask */
                factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'created_at'    => Carbon::now()->subHour(),
                    'kpi_missed_at' => Carbon::tomorrow(),
                ]);

                $jobStatus = $this->faker->randomElement(JobStatuses::$closedStatuses);
                $job->changeStatus($jobStatus);
            });

        $url      = action('Jobs\JobListController@upcomingKpi');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true);

        self::assertCount($jobsUpcomingKpiCount, $response->getData());
    }

    public function testUpcompingKPIWithActiveAndCompletedJobTaskStatus()
    {
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location);

        $jobsUpcomingKpiActiveCount = $this->faker->numberBetween(1, 3);
        $jobsUpcomingKpiCompletedCount = $this->faker->numberBetween(1, 3);

        //Jobs with upcoming KPI
        factory(Job::class, $jobsUpcomingKpiActiveCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                /** @var JobTask $jobTask */
                $jobTask = factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'created_at'    => Carbon::now()->subHour(),
                    'kpi_missed_at' => Carbon::tomorrow(),
                ]);
                $jobTask->changeStatus(JobTaskStatuses::ACTIVE);
            });

        factory(Job::class, $jobsUpcomingKpiCompletedCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                /** @var JobTask $jobTask */
                $jobTask = factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'created_at'    => Carbon::now()->subHour(),
                    'kpi_missed_at' => Carbon::tomorrow(),
                ]);
                $jobTask->changeStatus(JobTaskStatuses::COMPLETED);
            });

        // get upcoming before close
        $url      = action('Jobs\JobListController@upcomingKpi');
        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true);

        self::assertCount($jobsUpcomingKpiActiveCount, $response->getData());
    }

    /**
     * @throws \Throwable
     */
    public function testNoClosedJobsInMine()
    {
        $jobsCount = $this->faker->numberBetween(1, 3);

        /** @var Location $location */
        $location = factory(Location::class)->create();

        /** @var Job $job */
        $jobs = factory(Job::class, $jobsCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                $job->assignedUsers()->attach($this->user->id);
            });

        $url  = action('Jobs\JobListController@mine');
        $data = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true)
            ->getData();

        self::assertCount($jobsCount, $data);

        foreach ($jobs as $job) {
            $newStatus = $this->faker->randomElement(JobStatuses::$activeStatuses);
            $job->changeStatus($newStatus);
        }

        $data = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true)
            ->getData();

        self::assertCount($jobsCount, $data);

        foreach ($jobs as $job) {
            $newStatus = $this->faker->randomElement(JobStatuses::$closedStatuses);
            $job->changeStatus($newStatus);
        }

        $data = $this->getJson($url)
            ->assertStatus(200)
            ->getData();

        self::assertCount(0, $data);

        $this->user->locations()->attach($location->id);

        $url  = action('Jobs\JobListController@mineClosed');
        $data = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true)
            ->getData();

        self::assertCount($jobsCount, $data);
    }

    /**
     * @throws \Throwable
     */
    public function testNoClosedJobsInTeams()
    {
        /** @var Team $team */
        $team = factory(Team::class)->create();
        $team->users()->attach($this->user->id);

        $jobsCount = $this->faker->numberBetween(1, 3);

        /** @var Job $job */
        $jobs = factory(Job::class, $jobsCount)
            ->create()
            ->each(function (Job $job) use ($team) {
                $job->assignedTeams()->attach($team->id);
            });

        $url  = action('Jobs\JobListController@mineTeams', [
            'team_id' => $team->id,
        ]);
        $data = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true)
            ->getData();

        self::assertCount($jobsCount, $data);

        foreach ($jobs as $job) {
            $newStatus = $this->faker->randomElement(JobStatuses::$closedStatuses);
            $job->changeStatus($newStatus);
        }

        $data = $this->getJson($url)
            ->assertStatus(200)
            ->getData();

        self::assertCount(0, $data);
    }

    /**
     * @throws \Throwable
     */
    public function testNoClosedJobsInNoContact24()
    {
        /** @var Location $location */
        $location = factory(Location::class)->create();
        $this->user->locations()->attach($location->id);

        $jobsCount = $this->faker->numberBetween(1, 3);

        /** @var Job $job */
        $jobs = factory(Job::class, $jobsCount)
            ->create([
                'assigned_location_id' => $location->id,
            ])
            ->each(function (Job $job) {
                factory(JobTask::class)->create([
                    'job_id'        => $job->id,
                    'name'          => JobTaskTypes::INITIAL_CONTACT_KPI,
                    'kpi_missed_at' => Carbon::yesterday()->subDays($this->faker->numberBetween(1, 3)),
                ]);
            });

        $url  = action('Jobs\JobListController@noContact24Hours');
        $data = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(JobListResponse::class, true)
            ->getData();

        self::assertCount($jobsCount, $data);

        foreach ($jobs as $job) {
            $newStatus = $this->faker->randomElement(JobStatuses::$closedStatuses);
            $job->changeStatus($newStatus);
        }

        $data = $this->getJson($url)
            ->assertStatus(200)
            ->getData();

        self::assertCount(0, $data);
    }
}
