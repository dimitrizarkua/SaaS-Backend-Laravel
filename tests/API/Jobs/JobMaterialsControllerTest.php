<?php

namespace Tests\API\Jobs;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobStatus;
use App\Components\UsageAndActuals\Models\InsurerContractMaterial;
use App\Components\UsageAndActuals\Models\Material;
use App\Components\UsageAndActuals\Models\MeasureUnit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobMaterialsControllerTest
 *
 * @package App\Http\Controllers\Jobs
 */
class JobMaterialsControllerTest extends ApiTestCase
{

    protected $permissions = [
        'jobs.usage.view',
        'jobs.usage.materials.create',
        'jobs.usage.materials.update',
        'jobs.usage.materials.delete',
        'jobs.usage.materials.manage',
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
        factory(JobMaterial::class, $numberOfRecords)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobMaterialsController@index', ['job' => $job->id]);
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testShowMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobMaterial $model */
        $model = factory(JobMaterial::class)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobMaterialsController@show', [
            'job'          => $job->id,
            'job_material' => $model->id,
        ]);
        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['material_id'], $model->material_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['used_at'], $model->used_at->format('Y-m-d\TH:i:s\Z'));
        self::assertEquals($data['sell_cost_per_unit'], $model->sell_cost_per_unit);
        self::assertEquals($data['buy_cost_per_unit'], $model->buy_cost_per_unit);
        self::assertEquals($data['quantity_used'], $model->quantity_used);
        self::assertEquals($data['quantity_used_override'], $model->quantity_used_override);
        self::assertEquals($data['invoice_item_id'], $model->invoice_item_id);
    }

    public function testStoreMethod()
    {
        $job = factory(Job::class)->create();
        factory(JobStatus::class)->create([
            'job_id'  => $job->id,
            'status'  => JobStatuses::IN_PROGRESS,
            'user_id' => null,
        ]);
        $data = [
            'job_id'                 => $job->id,
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(1, 5),
            'quantity_used_override' => $this->faker->numberBetween(1, 10),
        ];

        $url      = action('Jobs\JobMaterialsController@store', ['job' => $job->id]);
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = JobMaterial::findOrFail($modelId);
        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['material_id'], $model->material_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['used_at']->toDateTimeString(), $model->used_at);
        self::assertEquals($data['quantity_used'], $model->quantity_used);
        self::assertEquals($data['quantity_used'], $model->quantity_used_override);
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
        /** @var JobMaterial $model */
        $model = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => $invoiceItem->id,
        ]);

        $url      = action('Jobs\JobMaterialsController@update', [
            'job'      => $model->job->id,
            'material' => $model->id,
        ]);
        $data     = [
            'job_id'                 => $job->id,
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(1, 5),
            'quantity_used_override' => $this->faker->numberBetween(1, 10),
            'invoice_item_id'        => $invoiceItem->id,
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = JobMaterial::findOrFail($model->id);
        self::assertEquals($data['material_id'], $reloaded->material_id);
        self::assertEquals($data['creator_id'], $reloaded->creator_id);
        self::assertEquals($data['quantity_used_override'], $reloaded->quantity_used_override);
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
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        $url = action('Jobs\JobMaterialsController@update', [
            'job'      => $job->id,
            'material' => $jobMaterial->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(JobMaterial::find($jobMaterial->id));
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
        $material = factory(Material::class)->create();
        $count    = $this->faker->numberBetween(2, 5);
        factory(JobMaterial::class, $count)->create([
            'material_id'     => $material->id,
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $insurerContractMaterial                     = new InsurerContractMaterial([
            'insurer_contract_id' => $job->insurer_contract_id,
            'material_id'         => $material->id,
        ]);
        $insurerContractMaterial->sell_cost_per_unit = $this->faker->randomFloat(2, 50, 200);
        $insurerContractMaterial->up_to_units        = $this->faker->numberBetween(2, 5);
        $insurerContractMaterial->save();

        $url = action('Jobs\JobMaterialsController@getTotalAmount', [
            'job' => $job->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
    }
}
