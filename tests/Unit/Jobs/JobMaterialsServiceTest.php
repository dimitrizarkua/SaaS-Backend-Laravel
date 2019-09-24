<?php

namespace App\Components\Jobs\Services;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\InvoiceStatus;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobMaterialsServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Models\VO\JobMaterialData;
use App\Components\UsageAndActuals\Models\InsurerContractMaterial;
use App\Components\UsageAndActuals\Models\Material;
use App\Components\UsageAndActuals\Models\MeasureUnit;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Class JobMaterialsServiceTest
 *
 * @package App\Components\Jobs\Services
 */
class JobMaterialsServiceTest extends TestCase
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobMaterialsServiceInterface
     */
    private $service;


    public function setUp()
    {
        parent::setUp();

        $this->models = array_merge([
            JobMaterial::class,
            Material::class,
            MeasureUnit::class,
        ], $this->models);


        $this->service = $this->app->get(JobMaterialsServiceInterface::class);
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
        $data = new JobMaterialData([
            'job_id'                 => $job->id,
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(1, 5),
            'quantity_used_override' => $this->faker->numberBetween(1, 10),
            'invoice_item_id'        => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        $jobMaterial = $this->service->create($data);

        self::assertEquals($data['job_id'], $jobMaterial->job_id);
        self::assertEquals($data['material_id'], $jobMaterial->material_id);
        self::assertEquals($data['creator_id'], $jobMaterial->creator_id);
        self::assertEquals($data['used_at'], $jobMaterial->used_at);
        self::assertEquals($data['quantity_used'], $jobMaterial->quantity_used);
        self::assertEquals($data['quantity_used'], $jobMaterial->quantity_used_override);
        self::assertEquals($data['invoice_item_id'], $jobMaterial->invoice_item_id);
        self::assertEquals($jobMaterial->quantity_used, $jobMaterial->quantity_used_override);
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
        $data = new JobMaterialData([
            'job_id'                 => $job->id,
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(1, 5),
            'quantity_used_override' => $this->faker->numberBetween(1, 10),
            'invoice_item_id'        => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->create($data);
    }

    public function testUpdateMethodSuccess()
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
        $data        = new JobMaterialData([
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(11, 15),
            'quantity_used_override' => $this->faker->numberBetween(11, 20),
        ]);

        $updatedJobMaterial = $this->service->update($jobMaterial, $data);

        self::assertEquals($data['material_id'], $updatedJobMaterial->material_id);
        self::assertEquals($data['creator_id'], $updatedJobMaterial->creator_id);
        self::assertEquals($data['used_at'], $updatedJobMaterial->used_at);
        self::assertEquals($data['quantity_used_override'], $updatedJobMaterial->quantity_used_override);
        self::assertNotEquals($updatedJobMaterial->quantity_used, $updatedJobMaterial->quantity_used_override);
    }

    public function testUpdateMethodFailWithInvalidCurrentJob()
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
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $data        = new JobMaterialData([
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(11, 15),
            'quantity_used_override' => $this->faker->numberBetween(11, 20),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update($jobMaterial, $data);
    }

    public function testUpdateMethodFailWithInvalidCurrentInvoice()
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
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $data        = new JobMaterialData([
            'material_id'            => factory(Material::class)->create()->id,
            'creator_id'             => factory(User::class)->create()->id,
            'used_at'                => Carbon::now(),
            'quantity_used'          => $this->faker->numberBetween(11, 15),
            'quantity_used_override' => $this->faker->numberBetween(11, 20),
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->update($jobMaterial, $data);
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
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        $this->service->delete($jobMaterial);

        self::assertNull(JobMaterial::find($jobMaterial->id));
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
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete($jobMaterial);
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
        $jobMaterial = factory(JobMaterial::class)->create([
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);

        self::expectException(NotAllowedException::class);
        $this->service->delete($jobMaterial);
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
        $material                                    = factory(Material::class)->create();
        $count                                       = $this->faker->numberBetween(2, 5);
        $jobMaterials                                = factory(JobMaterial::class, $count)->create([
            'material_id'     => $material->id,
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $insurerContractMaterial                     = new InsurerContractMaterial([
            'insurer_contract_id' => $job->insurer_contract_id,
            'material_id'         => $material->id,
        ]);
        $insurerContractMaterial->sell_cost_per_unit = $this->faker->randomFloat(2, 50, 100);
        $insurerContractMaterial->up_to_amount       = $this->faker->randomFloat(2, 50, 100) * $count;
        $insurerContractMaterial->save();

        $amounts = $this->service->calculateTotalAmountByJob($job->id);

        self::assertTrue(0 === bccomp($amounts['total_amount'], $insurerContractMaterial->up_to_amount, 2));
        self::assertTrue(0 === bccomp($amounts['total_amount_override'], $insurerContractMaterial->up_to_amount, 2));
    }

    public function testCalculateTotalAmountByJobMethodWithUpToUnits()
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
        $material                                    = factory(Material::class)->create();
        $count                                       = $this->faker->numberBetween(3, 5);
        $jobMaterials                                = factory(JobMaterial::class, $count)->create([
            'material_id'     => $material->id,
            'job_id'          => $job->id,
            'invoice_item_id' => factory(InvoiceItem::class)->create(['invoice_id' => $invoice->id])->id,
        ]);
        $insurerContractMaterial                     = new InsurerContractMaterial([
            'insurer_contract_id' => $job->insurer_contract_id,
            'material_id'         => $material->id,
        ]);
        $insurerContractMaterial->sell_cost_per_unit = $this->faker->randomFloat(2, 50, 100);
        $insurerContractMaterial->up_to_units        = $this->faker->numberBetween(1, 2);
        $insurerContractMaterial->save();

        $amounts = $this->service->calculateTotalAmountByJob($job->id);

        self::assertTrue(
            0 === bccomp(
                $amounts['total_amount'],
                (float)bcmul(
                    $insurerContractMaterial->up_to_units,
                    (string)$insurerContractMaterial->sell_cost_per_unit,
                    2
                ),
                2
            )
        );
        self::assertTrue(
            0 === bccomp(
                $amounts['total_amount_override'],
                (float)bcmul(
                    $insurerContractMaterial->up_to_units,
                    (string)$insurerContractMaterial->sell_cost_per_unit,
                    2
                ),
                2
            )
        );
    }
}
