<?php

namespace Tests\API\Jobs;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobStatus;
use App\Components\UsageAndActuals\Models\InsurerContractLabourType;
use App\Components\UsageAndActuals\Models\LabourType;
use App\Components\UsageAndActuals\Models\Material;
use App\Components\UsageAndActuals\Models\MeasureUnit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobLaboursControllerTest
 *
 * @package App\Http\Controllers\Jobs
 */
class JobLaboursControllerTest extends ApiTestCase
{

    protected $permissions = [
        'jobs.usage.view',
        'jobs.usage.labour.create',
        'jobs.usage.labour.update',
        'jobs.usage.labour.delete',
        'jobs.usage.labour.manage',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            JobMaterial::class,
            Material::class,
            MeasureUnit::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        $job             = factory(Job::class)->create();
        factory(JobLabour::class, $numberOfRecords)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobLaboursController@index', ['job' => $job->id]);
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testShowMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobLabour $model */
        $model = factory(JobLabour::class)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobLaboursController@show', [
            'job'        => $job->id,
            'job_labour' => $model->id,
        ]);
        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['labour_type_id'], $model->labour_type_id);
        self::assertEquals($data['worker_id'], $model->worker_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['started_at'], $model->started_at->format('Y-m-d\TH:i:s\Z'));
        self::assertEquals($data['ended_at'], $model->ended_at->format('Y-m-d\TH:i:s\Z'));
        self::assertEquals($data['started_at_override'], $model->started_at_override->format('Y-m-d\TH:i:s\Z'));
        self::assertEquals($data['ended_at_override'], $model->ended_at_override->format('Y-m-d\TH:i:s\Z'));
        self::assertEquals($data['break'], $model->break);
        self::assertEquals($data['first_tier_hourly_rate'], $model->first_tier_hourly_rate);
        self::assertEquals($data['second_tier_hourly_rate'], $model->second_tier_hourly_rate);
        self::assertEquals($data['third_tier_hourly_rate'], $model->third_tier_hourly_rate);
        self::assertEquals($data['fourth_tier_hourly_rate'], $model->fourth_tier_hourly_rate);
        self::assertEquals($data['calculated_total_amount'], $model->calculated_total_amount);
        self::assertEquals($data['invoice_item_id'], $model->invoice_item_id);
    }

    /**
     * @throws \Exception
     */
    public function testStoreMethod()
    {
        $job = factory(Job::class)->create();
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $data = [
            'labour_type_id'  => factory(LabourType::class)->create()->id,
            'worker_id'       => factory(User::class)->create()->id,
            'creator_id'      => factory(User::class)->create()->id,
            'started_at'      => Carbon::now()->subHour(),
            'ended_at'        => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'           => $this->faker->numberBetween(0, 59),
            'invoice_item_id' => factory(InvoiceItem::class)->create()->id,
        ];

        $url      = action('Jobs\JobLaboursController@store', ['job' => $job->id]);
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = JobLabour::findOrFail($modelId);
        self::assertEquals($job->id, $model->job_id);
        self::assertEquals($data['labour_type_id'], $model->labour_type_id);
        self::assertEquals($data['worker_id'], $model->worker_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['started_at']->toDateTimeString(), $model->started_at->toDateTimeString());
        self::assertEquals($data['ended_at']->toDateTimeString(), $model->ended_at->toDateTimeString());
        self::assertEquals($data['started_at']->toDateTimeString(), $model->started_at_override->toDateTimeString());
        self::assertEquals($data['ended_at']->toDateTimeString(), $model->ended_at_override->toDateTimeString());
        self::assertEquals($data['break'], $model->break);
        self::assertEquals($model->labourType->first_tier_hourly_rate, $model->first_tier_hourly_rate);
        self::assertEquals($model->labourType->second_tier_hourly_rate, $model->second_tier_hourly_rate);
        self::assertEquals($model->labourType->third_tier_hourly_rate, $model->third_tier_hourly_rate);
        self::assertEquals($model->labourType->fourth_tier_hourly_rate, $model->fourth_tier_hourly_rate);
        self::assertEquals(
            round($model->calculateTotalAmount(), 2),
            $model->calculated_total_amount
        );
    }

    public function testUpdateMethodMethod()
    {
        $job         = factory(Job::class)->create();
        $invoice     = factory(Invoice::class)->create(['job_id' => $job->id]);
        $invoiceItem = factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id]);
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        factory(InvoiceStatus::class)->create([
            'invoice_id' => $invoice->id,
            'status'     => FinancialEntityStatuses::DRAFT,
        ]);
        /** @var JobLabour $model */
        $model = factory(JobLabour::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => $invoiceItem->id,
        ]);

        $url      = action('Jobs\JobLaboursController@update', [
            'job'        => $model->job->id,
            'job_labour' => $model->id,
        ]);
        $data     = [
            'started_at_override' => Carbon::now()->subHour(),
            'ended_at_override'   => Carbon::now()->addMinutes($this->faker->numberBetween(60, 360)),
            'break'               => $this->faker->numberBetween(0, 59),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = JobLabour::findOrFail($model->id);
        self::assertEquals(
            $data['started_at_override']->toDateTimeString(),
            $reloaded->started_at_override->toDateTimeString()
        );
        self::assertEquals(
            $data['ended_at_override']->toDateTimeString(),
            $reloaded->ended_at_override->toDateTimeString()
        );
        self::assertEquals($data['break'], $reloaded->break);
    }

    public function testDestroyMethod()
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

        $url = action('Jobs\JobLaboursController@destroy', [
            'job'      => $job->id,
            'material' => $jobLabour->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(JobMaterial::find($jobLabour->id));
    }

    public function testGetTotalAmountMethod()
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
        $labour = factory(LabourType::class)->create();
        $count  = $this->faker->numberBetween(2, 5);
        factory(JobLabour::class, $count)->create([
            'labour_type_id'  => $labour->id,
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $insurerContractLabour                          = new InsurerContractLabourType([
            'insurer_contract_id' => $job->insurer_contract_id,
            'labour_type_id'      => $labour->id,
        ]);
        $insurerContractLabour->first_tier_hourly_rate  = $this->faker->randomFloat(2, 30, 40);
        $insurerContractLabour->second_tier_hourly_rate = $this->faker->randomFloat(2, 50, 80);
        $insurerContractLabour->third_tier_hourly_rate  = $this->faker->randomFloat(2, 100, 130);
        $insurerContractLabour->fourth_tier_hourly_rate = $this->faker->randomFloat(2, 150, 200);
        $insurerContractLabour->up_to_amount            = $this->faker->randomFloat(2, 50, 200);
        $insurerContractLabour->up_to_hours             = $this->faker->numberBetween(2, 5);
        $insurerContractLabour->save();

        $url = action('Jobs\JobLaboursController@getTotalAmount', [
            'job' => $job->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
    }
}
