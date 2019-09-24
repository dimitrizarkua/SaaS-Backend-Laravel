<?php

namespace Tests\API\Jobs;

use App\Components\Documents\Models\Document;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobReimbursement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\API\ApiTestCase;

/**
 * Class JobReimbursementsControllerTest
 *
 * @package App\Http\Controllers\Jobs
 */
class JobReimbursementsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.usage.view',
        'jobs.usage.reimbursements.manage',
        'jobs.usage.reimbursements.approve',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            JobReimbursement::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $job             = factory(Job::class)->create();
        $numberOfRecords = $this->faker->numberBetween(1, 3);
        factory(JobReimbursement::class, $numberOfRecords)->create(['job_id' => $job->id]);

        $url      = action('Jobs\JobReimbursementsController@index', ['job' => $job->id]);
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $job = factory(Job::class)->create();

        $data = [
            'user_id'         => factory(User::class)->create()->id,
            'creator_id'      => factory(User::class)->create()->id,
            'date_of_expense' => Carbon::now()->format('Y-m-d'),
            'document_id'     => factory(Document::class)->create()->id,
            'description'     => $this->faker->text,
            'total_amount'    => $this->faker->randomFloat(2, 50, 100),
            'is_chargeable'   => $this->faker->boolean,
        ];

        $url      = action('Jobs\JobReimbursementsController@store', ['job' => $job->id]);
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = JobReimbursement::findOrFail($modelId);
        self::assertEquals($model->job_id, $job->id);
        self::assertEquals($data['user_id'], $model->user_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['date_of_expense'], $model->date_of_expense->format('Y-m-d'));
        self::assertEquals($data['document_id'], $model->document_id);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['total_amount'], $model->total_amount);
        self::assertEquals($data['is_chargeable'], $model->is_chargeable);
    }

    public function testShowMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobReimbursement $model */
        $model = factory(JobReimbursement::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobReimbursementsController@show', [
            'job'                => $job->id,
            'job_reimbursements' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($model->job_id, $job->id);
        self::assertEquals($data['user_id'], $model->user_id);
        self::assertEquals($data['creator_id'], $model->creator_id);
        self::assertEquals($data['date_of_expense'], $model->date_of_expense->format('Y-m-d'));
        self::assertEquals($data['document_id'], $model->document_id);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['total_amount'], $model->total_amount);
        self::assertEquals($data['is_chargeable'], $model->is_chargeable);
    }

    public function testUpdateMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobReimbursement $model */
        $model = factory(JobReimbursement::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobReimbursementsController@update', [
            'job'                => $job->id,
            'job_reimbursements' => $model->id,
        ]);

        $data     = [
            'date_of_expense' => Carbon::now()->format('Y-m-d'),
            'description'     => $this->faker->text,
            'total_amount'    => $this->faker->randomFloat(2, 50, 100),
            'is_chargeable'   => $this->faker->boolean,
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = JobReimbursement::findOrFail($model->id);
        self::assertEquals($data['date_of_expense'], $reloaded->date_of_expense->format('Y-m-d'));
        self::assertEquals($data['description'], $reloaded->description);
        self::assertEquals($data['total_amount'], $reloaded->total_amount);
        self::assertEquals($data['is_chargeable'], $reloaded->is_chargeable);
    }

    public function testDestroyMethod()
    {
        $job = factory(Job::class)->create();
        /** @var JobReimbursement $model */
        $model = factory(JobReimbursement::class)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobReimbursementsController@destroy', [
            'job'                => $job->id,
            'job_reimbursements' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(JobReimbursement::find($model->id));
    }

    public function testApproveMethod()
    {
        $job = factory(Job::class)->create();

        /** @var JobReimbursement $model */
        $model = factory(JobReimbursement::class)->create([
            'job_id'      => $job->id,
            'approver_id' => null,
            'approved_at' => null,
        ]);

        $url = action('Jobs\JobReimbursementsController@approve', [
            'job'            => $job->id,
            'job_reimbursements' => $model->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $model = JobReimbursement::find($model->id);
        self::assertNotNull($model->approved_at);
        self::assertNotNull($model->approver_id);
    }
}
