<?php

namespace Tests\Unit\LaravelJobs\Jobs;

use App\Components\Jobs\Models\RecurringJob;
use App\Jobs\RecurringJobs\CreateJobFromRecurringJob;
use App\Jobs\RecurringJobs\ProcessRecurringJobs;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use Tests\TestCase;

/**
 * Class ProcessRecurringJobsTest
 *
 * @package Tests\Unit\Jobs
 * @group   jobs
 * @group   recurring
 */
class ProcessRecurringJobsTest extends TestCase
{
    /** @var ArrayTransformer */
    private $transformer;

    public function setUp()
    {
        parent::setUp();
        $transformer       = new ArrayTransformer;
        $transformerConfig = new ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);
        $this->transformer = $transformer;
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testCatchCreateJobFromRecurringJobEvent()
    {
        $rule = new Rule('FREQ=DAILY');
        $rule->setStartDate(Carbon::yesterday(), true);
        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob) {
                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    public function testDeleteRecurringJobWhichHasNoOccurrenceInTheFuture()
    {
        $startDate = Carbon::yesterday();
        $endDate   = Carbon::today();

        $rule = new Rule('FREQ=DAILY');
        $rule->setStartDate($startDate, true);
        $rule->setUntil($endDate);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob) {
                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $deletedRecurringJob = RecurringJob::withTrashed()
            ->find($recurringJob->id);

        self::assertNotNull($deletedRecurringJob->deleted_at);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    public function testDontDeleteRecurringJobWhichHasOccurrenceInTheFuture()
    {
        $startDate = Carbon::yesterday();
        $until   = Carbon::tomorrow();

        $rule = new Rule('FREQ=DAILY');
        $rule->setStartDate($startDate, true);
        $rule->setUntil($until);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob) {
                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $deletedRecurringJob = RecurringJob::withTrashed()
            ->find($recurringJob->id);

        self::assertNull($deletedRecurringJob->deleted_at);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    public function testOverYearTwoRecurrence()
    {
        $startDate = Carbon::createFromDate(2018, 12, 31);
        Carbon::setTestNow($startDate);

        $until = $startDate->copy()->addDay();

        $rule = new Rule('FREQ=DAILY');
        $rule->setStartDate($startDate, true)
            ->setUntil($until);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob) {
                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $deletedRecurringJob = RecurringJob::withTrashed()
            ->find($recurringJob->id);

        self::assertNull($deletedRecurringJob->deleted_at);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     * @throws \Exception
     */
    public function testWeeklyRule()
    {
        $startDate = Carbon::now();
        Carbon::setTestNow($startDate);

        $until = $startDate->copy()->addWeek(3);

        $rule = new Rule('FREQ=WEEKLY');
        $rule->setStartDate($startDate, true)
            ->setUntil($until);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob) {
                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $deletedRecurringJob = RecurringJob::withTrashed()
            ->find($recurringJob->id);

        self::assertNull($deletedRecurringJob->deleted_at);
    }

    /**
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function testProlongInfiniteJob()
    {
        $startDate = Carbon::now();
        Carbon::setTestNow($startDate->addHours(ProcessRecurringJobs::RECURR_LIB_LIMIT));
        $rule = new Rule('FREQ=HOURLY');
        $rule->setStartDate($startDate, true);

        $recurringJob = factory(RecurringJob::class)->create([
            'recurrence_rule' => $rule->getString(),
        ]);

        Queue::fake();

        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        Queue::assertPushedOn(
            'jobs',
            CreateJobFromRecurringJob::class,
            function (CreateJobFromRecurringJob $laravelJob) use ($recurringJob) {
                return $recurringJob->id === $laravelJob->getRecurringJobId();
            }
        );

        $deletedRecurringJob = RecurringJob::withTrashed()
            ->find($recurringJob->id);

        self::assertNull($deletedRecurringJob->deleted_at);
    }
}
