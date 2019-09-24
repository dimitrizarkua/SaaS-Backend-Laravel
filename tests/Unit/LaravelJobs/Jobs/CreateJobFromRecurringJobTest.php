<?php

namespace Tests\Unit\LaravelJobs\Jobs;

use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\RecurringJob;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use App\Jobs\RecurringJobs\CreateJobFromRecurringJob;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Tests\TestCase;

/**
 * Class CreateJobFromRecurringJobTest
 *
 * @package Tests\Unit\Jobs
 * @group   jobs
 */
class CreateJobFromRecurringJobTest extends TestCase
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobsServiceInterface
     */
    private $jobService;

    public function setUp()
    {
        parent::setUp();

        $this->jobService = Container::getInstance()->make(JobsServiceInterface::class);
    }

    /**
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function testCreateJobFromRecurringJob()
    {
        /** @var RecurringJob $recurringJob */
        $recurringJob = factory(RecurringJob::class)->create();
        $laravelJob = new CreateJobFromRecurringJob($recurringJob->id, Carbon::now());
        $laravelJob->handle($this->jobService);

        /** @var Job $job */
        $job = Job::query()
            ->where('recurring_job_id', $recurringJob->id)
            ->first();

        self::assertNotNull($job);
        self::assertEquals($recurringJob->id, $job->recurring_job_id);
        self::assertEquals($recurringJob->insurer_id, $job->insurer_id);
        self::assertEquals($recurringJob->site_address_id, $job->site_address_id);
        self::assertEquals($recurringJob->owner_location_id, $job->owner_location_id);
        self::assertEquals($recurringJob->description, $job->description);
        self::assertEquals($recurringJob->job_service_id, $job->job_service_id);
    }
}
