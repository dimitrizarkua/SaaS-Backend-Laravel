<?php

namespace App\Components\Jobs\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobLabourServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Models\VO\JobLabourData;
use App\Components\UsageAndActuals\Models\InsurerContractLabourType;
use App\Components\UsageAndActuals\Models\LabourType;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class JobLaboursServiceTest
 *
 * @package App\Components\Jobs\Services
 */
class JobLaboursServiceTest extends TestCase
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobLabourServiceInterface
     */
    private $service;


    public function setUp()
    {
        parent::setUp();

        $this->models = array_merge([
            JobLabour::class,
            LabourType::class,
        ], $this->models);

        $this->service = $this->app->get(JobLabourServiceInterface::class);
    }

    public function testCreateMethodSuccess()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create(['job_id' => $job->id]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $data = new JobLabourData([
            'job_id'          => $job->id,
            'labour_type_id'  => factory(LabourType::class)->create()->id,
            'worker_id'       => factory(User::class)->create()->id,
            'creator_id'      => factory(User::class)->create()->id,
            'started_at'      => Carbon::now()->subHour(),
            'ended_at'        => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'           => $this->faker->numberBetween(0, 59),
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        $jobLabour = $this->service->createJobLabour($data);

        self::assertEquals($data['job_id'], $jobLabour->job_id);
        self::assertEquals($data['labour_type_id'], $jobLabour->labour_type_id);
        self::assertEquals($data['worker_id'], $jobLabour->worker_id);
        self::assertEquals($data['creator_id'], $jobLabour->creator_id);
        self::assertEquals($data['started_at'], $jobLabour->started_at);
        self::assertEquals($jobLabour->started_at, $jobLabour->started_at_override);
        self::assertEquals($jobLabour->ended_at, $jobLabour->ended_at_override);
        self::assertEquals($data['ended_at'], $jobLabour->ended_at);
        self::assertEquals($data['break'], $jobLabour->break);
        self::assertEquals($data['invoice_item_id'], $jobLabour->invoice_item_id);
    }

    public function testCreateMethodFailWithInvalidJob()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create(['job_id' => $job->id]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::CLOSED,
            'user_id' => null,
        ]);
        $data = new JobLabourData([
            'job_id'          => $job->id,
            'labour_type_id'  => factory(LabourType::class)->create()->id,
            'worker_id'       => factory(User::class)->create()->id,
            'creator_id'      => factory(User::class)->create()->id,
            'started_at'      => Carbon::now()->subHour(),
            'ended_at'        => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'           => $this->faker->numberBetween(0, 59),
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->createJobLabour($data);
    }

    public function testCreateMethodFailWithInvalidWorkInterval()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create(['job_id' => $job->id]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $data = new JobLabourData([
            'job_id'          => $job->id,
            'labour_type_id'  => factory(LabourType::class)->create()->id,
            'worker_id'       => factory(User::class)->create()->id,
            'creator_id'      => factory(User::class)->create()->id,
            'started_at'      => Carbon::now(),
            'ended_at'        => Carbon::now()->subMinutes($this->faker->numberBetween(60, 360)),
            'break'           => $this->faker->numberBetween(0, 59),
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->createJobLabour($data);
    }

    public function testUpdateMethodSuccess()
    {
        $jobLabour = factory(JobLabour::class)->create();

        $data = new JobLabourData([
            'started_at_override' => Carbon::now()->subHour(),
            'ended_at_override'   => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'               => $this->faker->numberBetween(0, 59),
        ]);

        $jobMaterial = $this->service->updateJobLabour($jobLabour, $data);

        self::assertEquals($data['started_at_override'], $jobMaterial->started_at_override);
        self::assertEquals($data['ended_at_override'], $jobMaterial->ended_at_override);
        self::assertEquals($data['break'], $jobMaterial->break);
    }

    public function testUpdateMethodFailWithInvalidJob()
    {
        $job = factory(Job::class)->create();
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::CLOSED,
            'user_id' => null,
        ]);

        $jobLabour = factory(JobLabour::class)->create(['job_id' => $job->id]);

        $data = new JobLabourData([
            'started_at_override' => Carbon::now()->subHour(),
            'ended_at_override'   => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'               => $this->faker->numberBetween(0, 59),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->updateJobLabour($jobLabour, $data);
    }

    public function testUpdateMethodFailWithInvalidInvoice()
    {
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);
        $invoiceItem = factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id]);

        $jobLabour = factory(JobLabour::class)->create(['invoice_item_id' => $invoiceItem->id]);

        $data = new JobLabourData([
            'started_at_override' => Carbon::now()->subHour(),
            'ended_at_override'   => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'               => $this->faker->numberBetween(0, 59),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->updateJobLabour($jobLabour, $data);
    }

    public function testUpdateMethodFailWithInvalidWorkInterval()
    {
        $jobLabour = factory(JobLabour::class)->create();

        $data = new JobLabourData([
            'started_at_override' => Carbon::now(),
            'ended_at_override'   => Carbon::now()->subMinutes($this->faker->numberBetween(60, 360)),
            'break'               => $this->faker->numberBetween(0, 59),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->updateJobLabour($jobLabour, $data);
    }

    public function testDeleteMethodSuccess()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $jobLabour = factory(JobLabour::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        $this->service->deleteJobLabour($jobLabour);

        self::assertNull(JobLabour::find($jobLabour->id));
    }

    public function testDeleteMethodFailWithInvalidJob()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::CLOSED,
            'user_id' => null,
        ]);
        $jobLabour = factory(JobLabour::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->deleteJobLabour($jobLabour);
    }

    public function testDeleteMethodFailWithInvalidInvoice()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::APPROVED,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $jobLabour = factory(JobLabour::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->deleteJobLabour($jobLabour);
    }

    public function testCalculateTotalAmountByJobMethodWithUpToAmount()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $labourType                                         = factory(LabourType::class)->create();
        $count                                              = $this->faker->numberBetween(2, 5);
        $jobLabours                                         = factory(JobLabour::class, $count)->create([
            'labour_type_id'  => $labourType->id,
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $insurerContractLabourType                          = new InsurerContractLabourType([
            'insurer_contract_id' => $job->insurer_contract_id,
            'labour_type_id'      => $labourType->id,
        ]);
        $insurerContractLabourType->first_tier_hourly_rate  = $this->faker->randomFloat(2, 30, 40);
        $insurerContractLabourType->second_tier_hourly_rate = $this->faker->randomFloat(2, 50, 80);
        $insurerContractLabourType->third_tier_hourly_rate  = $this->faker->randomFloat(2, 100, 130);
        $insurerContractLabourType->fourth_tier_hourly_rate = $this->faker->randomFloat(2, 150, 200);
        $insurerContractLabourType->up_to_amount            = $this->faker->randomFloat(2, 50, 100);
        $insurerContractLabourType->save();

        $amount = $this->service->calculateTotalAmountByJob($job->id);

        self::assertTrue(0 === bccomp($amount, $insurerContractLabourType->up_to_amount, 2));
    }

    public function testCalculateTotalAmountByJobMethodWithUpToHours()
    {
        $job     = factory(Job::class)->create();
        $invoice = factory(Invoice::class)->create();
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $labourType                                         = factory(LabourType::class)->create();
        $count                                              = $this->faker->numberBetween(2, 5);
        $jobLabours                                         = factory(JobLabour::class, $count)->create([
            'labour_type_id'  => $labourType->id,
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $insurerContractLabourType                          = new InsurerContractLabourType([
            'insurer_contract_id' => $job->insurer_contract_id,
            'labour_type_id'      => $labourType->id,
        ]);
        $insurerContractLabourType->first_tier_hourly_rate  = $this->faker->randomFloat(2, 30, 40);
        $insurerContractLabourType->second_tier_hourly_rate = $this->faker->randomFloat(2, 50, 80);
        $insurerContractLabourType->third_tier_hourly_rate  = $this->faker->randomFloat(2, 100, 130);
        $insurerContractLabourType->fourth_tier_hourly_rate = $this->faker->randomFloat(2, 150, 200);
        $insurerContractLabourType->up_to_hours             = $this->faker->numberBetween(1, 2);
        $insurerContractLabourType->save();

        $amount = $this->service->calculateTotalAmountByJob($job->id);

        self::assertTrue(
            0 === bccomp(
                $amount,
                (float)bcmul(
                    $insurerContractLabourType->up_to_hours,
                    (string)$insurerContractLabourType->first_tier_hourly_rate,
                    2
                ),
                2
            )
        );
    }
}
