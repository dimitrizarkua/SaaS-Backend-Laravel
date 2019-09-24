<?php

namespace Tests\Integration\RecurringJob;

use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\RecurringJob;
use App\Jobs\RecurringJobs\CreateJobFromRecurringJob;
use App\Jobs\RecurringJobs\ProcessRecurringJobs;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Recurr\Rule;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class RecurringJobTest
 *
 * @package Tests\Integration\Notifications
 * @group   recurring
 */
class RecurringJobTest extends TestCase
{
    use JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobsServiceInterface
     */
    private $jobService;

    public function setUp()
    {
        parent::setUp();

        $this->jobService = Container::getInstance()->make(JobsServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->jobService);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCreateRecurringJobBasedOnRecurringJob()
    {
        $startDate = Carbon::now();

        $rule = new Rule('FREQ=WEEKLY');
        $rule->setStartDate($startDate, true);
        $rule->setUntil($startDate->copy()->addWeek(2));
        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob, $startDate) {
                $laravelCreateJobFromRecurringJob = new CreateJobFromRecurringJob($recurringJob->id, $startDate);
                $laravelCreateJobFromRecurringJob->handle($this->jobService);

                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
    }


    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testJobShouldNotCreatedByWeeklyRule()
    {
        $startDate = Carbon::now();

        $rule = new Rule('FREQ=WEEKLY');
        $rule->setStartDate($startDate, true);
        $rule->setUntil($startDate->copy()->addWeek(2));
        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();
        Carbon::setTestNow($startDate->copy()->addDay());

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertNotPushed('jobs');

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNull($job);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCreateJobIfTodayIsIntoInterval()
    {
        $startDate = Carbon::createFromDate(2018, 12, 1);
        Carbon::setTestNow($startDate);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $LaravelRecurringJob = new ProcessRecurringJobs();
        $LaravelRecurringJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob, $startDate) {
                $laravelCreateJobFromRecurringJob = new CreateJobFromRecurringJob($recurringJob->id, $startDate);
                $laravelCreateJobFromRecurringJob->handle($this->jobService);

                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
        self::assertEquals(
            $job->created_at->format(ProcessRecurringJobs::DATE_FORMAT),
            $startDate->format(ProcessRecurringJobs::DATE_FORMAT)
        );
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCreateJobIfTodayIsIntoSecondStepOfInterval()
    {
        $startDate = Carbon::createFromDate(2018, 12, 1);
        $today     = Carbon::create(2018, 12, 4, 23, 59, 59);
        Carbon::setTestNow($today);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob, $startDate) {
                $laravelCreateJobFromRecurringJob = new CreateJobFromRecurringJob($recurringJob->id, $startDate);
                $laravelCreateJobFromRecurringJob->handle($this->jobService);

                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
        self::assertEquals(
            $job->created_at->format(ProcessRecurringJobs::DATE_FORMAT),
            $startDate->format(ProcessRecurringJobs::DATE_FORMAT)
        );
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testShouldNotCreateJobIfOutOfInterval()
    {
        $startDate = Carbon::createFromDate(2018, 12, 1);
        $today     = Carbon::createFromDate(2018, 12, 3);
        Carbon::setTestNow($today);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertNotPushed(CreateJobFromRecurringJob::class);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testShouldNotCreateJobTwice()
    {
        $startDate = Carbon::createFromDate(2018, 12, 1);
        Carbon::setTestNow($startDate);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Event::fake();
        $job = factory(Job::class)->create([
            'recurring_job_id' => $recurringJob->id,
        ]);

        $job->created_at = $startDate;
        $job->save();

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertNotPushed(CreateJobFromRecurringJob::class);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCreateJobByFreqAndByDayRule()
    {
        $startDate = Carbon::createFromDate(2018, 12, 4); // 04 dec is Tuesday
        Carbon::setTestNow($startDate);

        $rule = new Rule('FREQ=DAILY;BYDAY=TU');
        $rule->setStartDate($startDate, true);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob, $startDate) {
                $LaravelCreateJobFromRecurringJob = new CreateJobFromRecurringJob($recurringJob->id, $startDate);
                $LaravelCreateJobFromRecurringJob->handle($this->jobService);

                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
        self::assertEquals(
            $job->created_at->format(ProcessRecurringJobs::DATE_FORMAT),
            $startDate->format(ProcessRecurringJobs::DATE_FORMAT)
        );
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testShouldNotCreateFreqAndByDayRule()
    {
        $startDate = Carbon::createFromDate(2018, 12, 3); // 03 dec is Monday
        Carbon::setTestNow($startDate);

        $rule = new Rule('FREQ=DAILY;BYDAY=TU');
        $rule->setStartDate($startDate, true);

        factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertNotPushed(CreateJobFromRecurringJob::class);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCreateJobIntervalThreeDaysAndByTimeRule()
    {
        $startDate = Carbon::create(2018, 12, 3, 8, 0, 0);
        $today     = Carbon::create(2018, 12, 4, 7, 59, 59);
        Carbon::setTestNow($today);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        Event::fake();
        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob, $startDate) {
                $LaravelCreateJobFromRecurringJob = new CreateJobFromRecurringJob($recurringJob->id, $startDate);
                $LaravelCreateJobFromRecurringJob->handle($this->jobService);

                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
        self::assertEquals(
            $job->created_at->format(ProcessRecurringJobs::DATE_FORMAT),
            $startDate->format(ProcessRecurringJobs::DATE_FORMAT)
        );
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Throwable
     */
    public function testCreateJobIntervalThreeDaysAndByTimeRuleOneSecondBefore()
    {
        $startDate = Carbon::create(2018, 12, 3, 8, 0, 0);
        $today     = Carbon::create(2018, 12, 7, 7, 59, 59);
        Carbon::setTestNow($today);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        Event::fake();
        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob, $startDate) {
                $LaravelCreateJobFromRecurringJob = new CreateJobFromRecurringJob($recurringJob->id, $startDate);
                $LaravelCreateJobFromRecurringJob->handle($this->jobService);

                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
        self::assertEquals(
            $job->created_at->format(ProcessRecurringJobs::DATE_FORMAT),
            $startDate->format(ProcessRecurringJobs::DATE_FORMAT)
        );
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testShouldNotCreateIntervalThreeDaysAndByTimeRule()
    {
        $startDate = Carbon::create(2018, 12, 3, 8, 0, 0);
        $today     = Carbon::create(2018, 12, 4, 9, 0, 1);
        Carbon::setTestNow($today);

        $rule = new Rule('FREQ=DAILY;INTERVAL=3');
        $rule->setStartDate($startDate, true);

        factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertNotPushed(CreateJobFromRecurringJob::class);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCreateMultipleJobsByHourlyRule()
    {
        $startDate = Carbon::create(2018, 12, 3, 8, 0, 0);
        $today     = Carbon::create(2018, 12, 4, 7, 59, 59);
        Carbon::setTestNow($today);

        $rule = new Rule('FREQ=HOURLY');
        $rule->setStartDate($startDate, true);

        Event::fake();
        factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelRecurringJob = new ProcessRecurringJobs();
        $laravelRecurringJob->handle();

        Queue::assertPushed(CreateJobFromRecurringJob::class, 24);
    }
}
