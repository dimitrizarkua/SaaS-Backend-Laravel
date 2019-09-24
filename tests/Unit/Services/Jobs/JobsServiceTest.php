<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Enums\JobTaskTypes;
use App\Components\Jobs\Events\JobCreated;
use App\Components\Jobs\Events\JobPinToggled;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Jobs\Models\VO\JobCreationData;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\InsurerContract;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobsServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobsServiceTest extends TestCase
{
    use JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobsServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobsServiceInterface::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateJobWithClaimNumberOnly(): void
    {
        Event::fake();
        $jobDataInstance = new JobCreationData();

        $createdJob = $this->service->createJob($jobDataInstance);

        $job = Job::findOrFail($createdJob->id);
        self::assertEquals($createdJob->claim_number, $job->claim_number);
        self::assertEquals($jobDataInstance->getClaimNumber(), $createdJob->claim_number);

        Event::assertDispatched(JobCreated::class);
    }

    /**
     * @throws \Throwable
     */
    public function testJobTaskShouldBeCreated(): void
    {
        Event::fake();
        /** @var JobTaskType $jobTaskType */
        $jobTaskType     = factory(JobTaskType::class)->create([
            'name'                     => JobTaskTypes::INITIAL_CONTACT_KPI,
            'can_be_scheduled'         => false,
            'allow_edit_due_date'      => true,
            'default_duration_minutes' => 0,
            'kpi_hours'                => 24,
            'kpi_include_afterhours'   => false,
            'auto_create'              => true,
        ]);
        $jobDataInstance = new JobCreationData();

        $createdJob = $this->service->createJob($jobDataInstance);
        $createdJob->fresh();

        self::assertCount(1, $createdJob->tasks);
        /** @var JobTask $task */
        $task = $createdJob->tasks->first();

        self::assertEquals($jobTaskType->name, $task->name);
        self::assertNotNull($task->kpi_missed_at);
        self::assertInstanceOf(\Illuminate\Support\Carbon::class, $task->kpi_missed_at);
        Event::assertDispatched(JobCreated::class);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateFullJobWithNewStatus(): void
    {
        $jobDataInstance = FakeJobsDataFactory::getFullJobDataInstance();
        $expectedStatus  = JobStatuses::NEW;
        $this->createContract($jobDataInstance->insurer_id);
        $createdJob = $this->service->createJob($jobDataInstance);

        $job = Job::findOrFail($createdJob->id);
        self::assertEquals($jobDataInstance->getClaimNumber(), $createdJob->claim_number);
        self::assertEquals($createdJob->claim_number, $job->claim_number);

        JobStatus::query()
            ->where([
                'job_id' => $createdJob->id,
                'status' => $expectedStatus,
            ])
            ->firstOrFail();

        self::assertEquals(1, $createdJob->statuses()->count());
        self::assertEquals($expectedStatus, $createdJob->latestStatus()->value('status'));
    }

    /**
     * @throws \Throwable
     */
    public function testCreateJobWithNonDefaultStatus(): void
    {
        $jobDataInstance = FakeJobsDataFactory::getFullJobDataInstance();
        $expectedStatus  = JobStatuses::IN_PROGRESS;
        $this->createContract($jobDataInstance->insurer_id);
        $createdJob = $this->service->createJob($jobDataInstance, $expectedStatus);

        Job::findOrFail($createdJob->id);

        JobStatus::query()
            ->where([
                'job_id' => $createdJob->id,
                'status' => $expectedStatus,
            ])
            ->firstOrFail();

        self::assertEquals(1, $createdJob->statuses()->count());
        self::assertEquals($expectedStatus, $createdJob->latestStatus()->value('status'));
    }

    /**
     * @throws \Throwable
     */
    public function testCreateJobWithNonEmptyUser(): void
    {
        $jobDataInstance = FakeJobsDataFactory::getFullJobDataInstance();
        $assigner        = factory(User::class)->create();
        $this->createContract($jobDataInstance->insurer_id);
        $createdJob = $this->service->createJob($jobDataInstance, JobStatuses::NEW, $assigner->id);

        Job::findOrFail($createdJob->id);

        JobStatus::query()
            ->where([
                'job_id'  => $createdJob->id,
                'user_id' => $assigner->id,
            ])
            ->firstOrFail();

        self::assertEquals($assigner->id, $createdJob->latestStatus()->value('user_id'));
    }

    /**
     * @throws \Throwable
     */
    public function testCreateJobWasTouched(): void
    {
        $jobDataInstance = FakeJobsDataFactory::getFullJobDataInstance();
        $this->createContract($jobDataInstance->insurer_id);
        $createdJob = $this->service->createJob($jobDataInstance);

        $job = Job::findOrFail($createdJob->id);
        self::assertNotNull($job->touched_at);
        self::assertNotNull($createdJob->touched_at);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToCreateJobWithInvalidStatus(): void
    {
        $jobDataInstance = FakeJobsDataFactory::getFullJobDataInstance();
        $this->createContract($jobDataInstance->insurer_id);
        $this->expectException(InvalidArgumentException::class);
        $this->service->createJob($jobDataInstance, 'INVALID');
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteJob(): void
    {
        Event::fake();
        $job = $this->fakeJobWithStatus();

        $this->service->deleteJob($job);

        $this->expectException(ModelNotFoundException::class);
        Job::findOrFail($job->id);

        Event::assertDispatched(JobCreated::class);
    }

    public function testGetJob(): void
    {
        $createdJob = factory(Job::class)->create();

        $job = $this->service->getJob($createdJob->id);

        self::assertEquals($createdJob->id, $job->id);
        self::assertEquals($createdJob->claim_number, $job->claim_number);
    }

    public function testFailToGetNonExistingJob(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->getJob(0);
    }

    /**
     * @throws \Throwable
     */
    public function testGetJobStatus(): void
    {
        $job       = factory(Job::class)->create();
        $jobStatus = factory(JobStatus::class)->create([
            'job_id' => $job->id,
        ]);

        $currentStatus = $this->service->getJobStatus($job->id);

        self::assertEquals($job->latestStatus()->value('status'), $currentStatus);
        self::assertEquals($jobStatus->status, $currentStatus);
    }

    /**
     * @throws \Throwable
     */
    public function testPinJob(): void
    {
        Event::fake();

        $job = factory(Job::class)->create();

        $this->service->pin($job->id);

        $pinnedJob = Job::findOrFail($job->id);
        self::assertNull($job->pinned_at);
        self::assertNotNull($pinnedJob->pinned_at);

        Event::assertDispatched(JobPinToggled::class);
    }

    /**
     * @throws \Throwable
     */
    public function testUnpinJob(): void
    {
        $job = factory(Job::class)->create([
            'pinned_at' => Carbon::now(),
        ]);

        $this->service->pin($job->id, false);

        $unpinnedJob = Job::findOrFail($job->id);
        self::assertNull($unpinnedJob->pinned_at);
    }

    /**
     * @throws \Throwable
     */
    public function testTouchJob(): void
    {
        $job = factory(Job::class)->create();

        $this->service->touch($job->id);

        $touchedJob = Job::findOrFail($job->id);
        self::assertNotNull($job->touched_at);
        self::assertNotNull($touchedJob->touched_at);
        self::assertTrue($touchedJob->touched_at->greaterThanOrEqualTo($job->touched_at));
    }

    /**
     * @throws \Throwable
     */
    public function testSnoozeJob(): void
    {
        $job          = $this->fakeJobWithStatus();
        $snoozedUntil = $this->faker->date();

        $this->service->snoozeJob($job->id, $snoozedUntil);

        $snoozedJob = Job::findOrFail($job->id);
        self::assertEquals($snoozedUntil, $snoozedJob->snoozed_until->toDateString());
    }

    public function testFailSnoozeClosedJob(): void
    {
        $job          = $this->fakeJobWithStatus(JobStatuses::CLOSED);
        $snoozedUntil = $this->faker->date();

        $this->expectException(NotAllowedException::class);
        $this->service->snoozeJob($job->id, $snoozedUntil);
    }

    /**
     * @throws \Throwable
     */
    public function testUnsnoozeJob(): void
    {
        $job = $this->fakeJobWithStatus(null, [
            'snoozed_until' => $this->faker->date(),
        ]);

        $this->service->unsnoozeJob($job->id);

        $unsnoozedJob = Job::findOrFail($job->id);
        self::assertNull($unsnoozedJob->snoozed_until);
    }

    public function testFailUnsnoozeClosedJob(): void
    {
        $job = $this->fakeJobWithStatus(JobStatuses::CLOSED);

        $this->expectException(NotAllowedException::class);
        $this->service->unsnoozeJob($job->id);
    }

    public function testGetCostingCounters(): void
    {
        $job = $this->fakeJobWithStatus();
        factory(JobMaterial::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobLabour::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobAllowance::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobReimbursement::class)->create([
            'job_id' => $job->id,
        ]);
        factory(JobLahaCompensation::class)->create([
            'job_id' => $job->id,
        ]);
        factory(PurchaseOrder::class)->create([
            'job_id' => $job->id,
        ]);

        $counters = $this->service->getJobCostingCounters($job->id);

        self::assertEquals(1, $counters['materials']);
        self::assertEquals(1, $counters['equipment']);
        //labour, allowance, reimbursement, laha compensation
        self::assertEquals(4, $counters['time']);
        self::assertEquals(1, $counters['purchase_orders']);
    }

    /**
     * @param int $insurerId
     *
     * @return InsurerContract
     * @throws \JsonMapper_Exception
     */
    private function createContract(int $insurerId): InsurerContract
    {
        return app()->make(InsurerContractsInterface::class)->createContract(new InsurerContractData(
            [
                'contact_id'       => $insurerId,
                'contract_number'  => $this->faker->bankAccountNumber,
                'description'      => $this->faker->text,
                'effect_date'      => Carbon::now()->subDays(3)->format('Y-m-d'),
                'termination_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
            ]
        ));
    }
}
