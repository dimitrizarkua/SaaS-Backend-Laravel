<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobEquipmentChargingInterval;
use App\Components\Jobs\Resources\FullJobEquipmentResource;
use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\Unit\UsageAndActuals\EquipmentTestFactory;

/**
 * Class JobEquipmentControllerTest
 *
 * @package Tests\API\Jobs
 * @group   api
 * @group   jobs
 * @group   equipment
 */
class JobEquipmentControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.usage.view',
        'jobs.usage.equipment.create',
        'jobs.usage.equipment.update',
        'jobs.usage.equipment.delete',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $models       = [
            EquipmentCategoryChargingInterval::class,
            JobEquipmentChargingInterval::class,
            JobEquipment::class,
            EquipmentCategory::class,
            Equipment::class,
            Location::class,
            User::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexJobEquipmentMethod(): void
    {
        $job   = $this->fakeJobWithStatus();
        $count = $this->faker->numberBetween(1, 5);
        factory(JobEquipment::class, $count)->create([
            'job_id' => $job->id,
        ]);
        $url = action('Jobs\JobEquipmentController@index', [
            'job_id' => $job->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($count);
    }

    public function testCreateJobEquipmentMethod(): void
    {
        $job       = $this->fakeJobWithStatus();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval();
        $startedAt = Carbon::now();
        $endedAt   = (new Carbon($startedAt))->addDay();
        $request   = [
            'equipment_id' => $equipment->id,
            'started_at'   => $startedAt,
            'ended_at'     => $endedAt,
        ];
        $url       = action('Jobs\JobEquipmentController@store', [
            'job_id' => $job->id,
        ]);

        $response = $this->postJson($url, $request)
            ->assertStatus(201)
            ->assertSeeData()
            ->assertValidSchema(FullJobEquipmentResource::class);
        $data     = $response->getData();
        $model    = JobEquipment::findOrFail($data['id']);

        self::assertEquals($model->job_id, $job->id);
        self::assertEquals($model->equipment_id, $data['equipment_id']);
        self::assertEquals($model->creator_id, $this->user->id);
        self::assertEquals($model->started_at, new Carbon($request['started_at']));
        self::assertEquals($model->ended_at, new Carbon($request['ended_at']));
        self::assertGreaterThan(0, $model->intervals_count);
        self::assertGreaterThan(0, $model->intervals_count_override);
        self::assertNull($model->invoice_item_id);
    }

    public function testCreateJobEquipmentMethodReturnsValidationErrorWhenWrongRequest(): void
    {
        $job  = $this->fakeJobWithStatus();
        $data = [
            'equipment_id' => null,
            'started_at'   => $this->faker->word,
            'ended_at'     => $this->faker->word,
        ];
        $url  = action('Jobs\JobEquipmentController@store', [
            'job_id' => $job->id,
        ]);

        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testCreateJobEquipmentMethodReturnsValidationErrorWhenEndedAtLessThanStartedAt(): void
    {
        $job       = $this->fakeJobWithStatus();
        $equipment = EquipmentTestFactory::createEquipmentWithInterval();
        $startedAt = Carbon::now();
        $endedAt   = (new Carbon($startedAt))->subDay();
        $data      = [
            'equipment_id' => $equipment->id,
            'started_at'   => $startedAt,
            'ended_at'     => $endedAt,
        ];
        $url       = action('Jobs\JobEquipmentController@store', [
            'job_id' => $job->id,
        ]);

        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testShowJobEquipmentMethod(): void
    {
        $job   = $this->fakeJobWithStatus();
        $model = EquipmentTestFactory::createJobEquipmentWithInterval($job->id);
        $url   = action('Jobs\JobEquipmentController@show', [
            'job_id'           => $model->job_id,
            'job_equipment_id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200)
            ->assertValidSchema(FullJobEquipmentResource::class);
        $data     = $response->getData();

        self::assertEquals($model->job_id, $data['job_id']);
        self::assertEquals($model->equipment_id, $data['equipment_id']);
        self::assertEquals($model->creator_id, $data['creator_id']);
        self::assertEquals($model->started_at, new Carbon($data['started_at']));
        self::assertEquals($model->ended_at, new Carbon($data['ended_at']));
        self::assertEquals($model->interval, $data['interval']);
        self::assertEquals($model->intervals_count, $data['intervals_count']);
        self::assertEquals($model->intervals_count_override, $data['intervals_count_override']);
        self::assertEquals($model->buy_cost_per_interval, $data['buy_cost_per_interval']);
        self::assertEquals($model->invoice_item_id, $data['invoice_item_id']);
    }

    public function testFinishUsingJobEquipmentMethod(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'     => $job->id,
            'creator_id' => $this->user->id,
            'ended_at'   => null,
        ]);
        $request      = [
            'ended_at' => (new Carbon($jobEquipment->started_at))->addDays(1),
        ];
        $url          = action('Jobs\JobEquipmentController@finishUsing', [
            'job_id'           => $job->id,
            'job_equipment_id' => $jobEquipment->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200);
        $data     = $response->getData();
        $model    = JobEquipment::findOrFail($data['id']);

        self::assertEquals($model->ended_at, new Carbon($request['ended_at']));
        self::assertGreaterThan(0, $model->intervals_count);
        self::assertGreaterThan(0, $model->intervals_count_override);
    }

    public function testFinishUsingMethodRequiresAdditionalPermissionWhenEquipmentNotCreatedByCurrentUser(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'   => $job->id,
            'ended_at' => null,
        ]);
        $request      = [
            'ended_at' => (new Carbon($jobEquipment->started_at))->addDays(1),
        ];
        $url          = action('Jobs\JobEquipmentController@finishUsing', [
            'job_id'           => $job->id,
            'job_equipment_id' => $jobEquipment->id,
        ]);

        $this->patchJson($url, $request)
            ->assertStatus(403);

        /** @var \App\Components\RBAC\Models\Role $role */
        $role = $this->user->roles()->first();
        $role->permissions()->create([
            'permission' => 'jobs.usage.equipment.manage',
        ]);

        $this->patchJson($url, $request)
            ->assertStatus(200);
    }

    public function testOverrideJobEquipmentIntervalsCountMethod(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'     => $job->id,
            'creator_id' => $this->user->id,
        ]);
        $request      = [
            'intervals_count_override' => $this->faker->numberBetween(1, 9),
        ];
        $url          = action('Jobs\JobEquipmentController@overrideIntervalsCount', [
            'job_id'           => $job->id,
            'job_equipment_id' => $jobEquipment->id,
        ]);

        $response = $this->patchJson($url, $request)
            ->assertStatus(200);
        $data     = $response->getData();
        $model    = JobEquipment::findOrFail($data['id']);

        self::assertEquals($model->intervals_count, $jobEquipment->intervals_count);
        self::assertEquals($model->intervals_count_override, $request['intervals_count_override']);
    }

    public function testOverrideIntervalsCountRequiresAdditionalPermissionWhenEquipmentNotCreatedByCurrentUser(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        $request      = [
            'intervals_count_override' => $this->faker->numberBetween(1, 9),
        ];
        $url          = action('Jobs\JobEquipmentController@overrideIntervalsCount', [
            'job_id'           => $job->id,
            'job_equipment_id' => $jobEquipment->id,
        ]);

        $this->patchJson($url, $request)
            ->assertStatus(403);

        /** @var \App\Components\RBAC\Models\Role $role */
        $role = $this->user->roles()->first();
        $role->permissions()->create([
            'permission' => 'jobs.usage.equipment.manage',
        ]);

        $this->patchJson($url, $request)
            ->assertStatus(200);
    }

    public function testFailToOverrideJobEquipmentIntervalsCountMethodWhenCountIsLessThanOne(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id'     => $job->id,
            'creator_id' => $this->user->id,
        ]);
        $request      = [
            'intervals_count_override' => $this->faker->numberBetween(-10, 0),
        ];
        $url          = action('Jobs\JobEquipmentController@overrideIntervalsCount', [
            'job_id'           => $job->id,
            'job_equipment_id' => $jobEquipment->id,
        ]);

        $this->patchJson($url, $request)
            ->assertStatus(422);
    }

    public function testDestroyJobEquipmentMethod(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $model */
        $model = factory(JobEquipment::class)->create([
            'job_id'     => $job->id,
            'creator_id' => $this->user->id,
        ]);
        $url   = action('Jobs\JobEquipmentController@destroy', [
            'job_id'           => $job->id,
            'job_equipment_id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::assertNull(JobEquipment::find($model->id));
    }

    public function testDestroyMethodRequiresAdditionalPermissionWhenEquipmentNotCreatedByCurrentUser(): void
    {
        $job = $this->fakeJobWithStatus();
        /** @var JobEquipment $jobEquipment */
        $jobEquipment = factory(JobEquipment::class)->create([
            'job_id' => $job->id,
        ]);
        $url          = action('Jobs\JobEquipmentController@destroy', [
            'job_id'           => $job->id,
            'job_equipment_id' => $jobEquipment->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(403);

        /** @var \App\Components\RBAC\Models\Role $role */
        $role = $this->user->roles()->first();
        $role->permissions()->create([
            'permission' => 'jobs.usage.equipment.manage',
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::assertNull(JobEquipment::find($jobEquipment->id));
    }

    public function testGetJobEquipmentTotalAmountMethod(): void
    {
        $job = $this->fakeJobWithStatus();
        $url = action('Jobs\JobEquipmentController@getTotalAmount', [
            'job' => $job->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200);

        $data = $response->getData();

        self::assertArrayHasKey('total_amount', $data);
        self::assertArrayHasKey('total_amount_for_insurer', $data);
    }
}
