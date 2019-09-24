<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\UsageAndActuals\Models\LahaCompensation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobLahaCompensationsControllerTest
 *
 * @package App\Http\Controllers\Jobs
 */
class JobLahaCompensationsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.usage.view',
        'jobs.usage.laha.manage',
        'jobs.usage.laha.approve',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            JobLahaCompensation::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $job             = factory(Job::class)->create();
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(JobLahaCompensation::class, $numberOfRecords)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobLahaCompensationsController@index', ['job' => $job->id]);
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $job = factory(Job::class)->create();
        /** @var LahaCompensation $lahaCompensation */
        $lahaCompensation = factory(LahaCompensation::class)->create();

        $data = [
            'job_id'               => $job->id,
            'user_id'              => factory(User::class)->create()->id,
            'creator_id'           => factory(User::class)->create()->id,
            'laha_compensation_id' => $lahaCompensation->id,
            'date_started'         => Carbon::now()->format('Y-m-d'),
            'days'                 => $this->faker->numberBetween(1, 5),
        ];

        $url      = action('Jobs\JobLahaCompensationsController@store', ['job' => $job->id]);
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = JobLahaCompensation::findOrFail($modelId);
        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['user_id'], $model->user_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['laha_compensation_id'], $model->laha_compensation_id);
        self::assertEquals($data['date_started'], $model->date_started->format('Y-m-d'));
        self::assertEquals($lahaCompensation->rate_per_day, $model->rate_per_day);
        self::assertEquals($data['days'], $model->days);
    }

    public function testShowMethod()
    {
        $job = factory(Job::class)->create();

        /** @var JobLahaCompensation $model */
        $model = factory(JobLahaCompensation::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobLahaCompensationsController@show', [
            'job'                   => $job->id,
            'job_laha_compensation' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['job_id'], $model->job_id);
        self::assertEquals($data['user_id'], $model->user_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['laha_compensation_id'], $model->laha_compensation_id);
        self::assertEquals($data['date_started'], $model->date_started->format('Y-m-d'));
        self::assertEquals($data['rate_per_day'], $model->rate_per_day);
        self::assertEquals($data['days'], $model->days);
    }

    public function testUpdateMethod()
    {
        $job = factory(Job::class)->create();

        /** @var JobLahaCompensation $model */
        $model = factory(JobLahaCompensation::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobLahaCompensationsController@update', [
            'job'                   => $job->id,
            'job_laha_compensation' => $model->id,
        ]);

        $data     = [
            'date_started' => Carbon::now()->format('Y-m-d'),
            'days'         => $this->faker->numberBetween(1, 5),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = JobLahaCompensation::findOrFail($model->id);
        self::assertEquals($data['date_started'], $reloaded->date_started->format('Y-m-d'));
        self::assertEquals($data['days'], $reloaded->days);
    }

    public function testDestroyMethod()
    {
        $job = factory(Job::class)->create();

        /** @var JobLahaCompensation $model */
        $model = factory(JobLahaCompensation::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobLahaCompensationsController@destroy', [
            'job'                   => $job->id,
            'job_laha_compensation' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(JobLahaCompensation::find($model->id));
    }

    public function testApproveMethod()
    {
        $job = factory(Job::class)->create();

        /** @var JobLahaCompensation $model */
        $model = factory(JobLahaCompensation::class)->create([
            'job_id'      => $job->id,
            'approver_id' => null,
            'approved_at' => null,
        ]);

        $url = action('Jobs\JobLahaCompensationsController@approve', [
            'job'                   => $job->id,
            'job_laha_compensation' => $model->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $model = JobLahaCompensation::find($model->id);
        self::assertNotNull($model->approved_at);
        self::assertNotNull($model->approver_id);
    }
}
