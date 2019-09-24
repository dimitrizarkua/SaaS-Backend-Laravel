<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\UsageAndActuals\Models\AllowanceType;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobAllowancesControllerTest
 *
 * @package App\Http\Controllers\Jobs
 */
class JobAllowancesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.usage.view',
        'jobs.usage.allowances.manage',
        'jobs.usage.allowances.approve',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            JobAllowance::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $job             = factory(Job::class)->create();
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(JobAllowance::class, $numberOfRecords)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobAllowancesController@index', ['job' => $job->id]);
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $job = factory(Job::class)->create();
        /** @var AllowanceType $allowanceType */
        $allowanceType = factory(AllowanceType::class)->create();

        $data = [
            'job_id'            => $job->id,
            'user_id'           => factory(User::class)->create()->id,
            'creator_id'        => factory(User::class)->create()->id,
            'allowance_type_id' => $allowanceType->id,
            'date_given'        => Carbon::now()->format('Y-m-d'),
            'amount'            => $this->faker->numberBetween(1, 5),
        ];

        $url      = action('Jobs\JobAllowancesController@store', ['job' => $job->id]);
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = JobAllowance::findOrFail($modelId);
        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['user_id'], $model->user_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['allowance_type_id'], $model->allowance_type_id);
        self::assertEquals($data['date_given'], $model->date_given->format('Y-m-d'));
        self::assertEquals($allowanceType->charge_rate_per_interval, $model->charge_rate_per_interval);
        self::assertEquals($data['amount'], $model->amount);
    }

    public function testShowMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobAllowance $model */
        $model = factory(JobAllowance::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobAllowancesController@show', [
            'job'            => $job->id,
            'job_allowances' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['user_id'], $model->user_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['allowance_type_id'], $model->allowance_type_id);
        self::assertEquals($data['date_given'], $model->date_given->format('Y-m-d'));
        self::assertEquals($data['charge_rate_per_interval'], $model->charge_rate_per_interval);
        self::assertEquals($data['amount'], $model->amount);
    }

    public function testUpdateMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobAllowance $model */
        $model = factory(JobAllowance::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobAllowancesController@update', [
            'job'            => $job->id,
            'job_allowances' => $model->id,
        ]);

        $data     = [
            'date_given' => Carbon::now()->format('Y-m-d'),
            'amount'     => $this->faker->numberBetween(1, 5),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = JobAllowance::findOrFail($model->id);
        self::assertEquals($data['date_given'], $reloaded->date_given->format('Y-m-d'));
        self::assertEquals($data['amount'], $reloaded->amount);
    }

    public function testDestroyMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobAllowance $model */
        $model = factory(JobAllowance::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobAllowancesController@destroy', [
            'job'            => $job->id,
            'job_allowances' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(JobAllowance::find($model->id));
    }

    public function testApproveMethod()
    {
        $job = factory(Job::class)->create();

        /** @var JobAllowance $model */
        $model = factory(JobAllowance::class)->create([
            'job_id'      => $job->id,
            'approver_id' => null,
            'approved_at' => null,
        ]);

        $url = action('Jobs\JobAllowancesController@approve', [
            'job'            => $job->id,
            'job_allowances' => $model->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $model = JobAllowance::find($model->id);
        self::assertNotNull($model->approved_at);
        self::assertNotNull($model->approver_id);
    }
}
