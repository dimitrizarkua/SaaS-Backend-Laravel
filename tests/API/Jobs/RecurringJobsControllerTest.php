<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\RecurringJob;
use Tests\API\ApiTestCase;
use Tests\Unit\Services\Jobs\FakeJobsDataFactory;

/**
 * Class RecurringJobsControllerTest
 *
 * @package Tests\API\Jobs
 * @group   recurring
 */
class RecurringJobsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_recurring',
    ];

    /**
     * @throws \JsonMapper_Exception
     * @throws \Recurr\Exception\InvalidRRule
     */
    public function testCreateRecord()
    {
        $recurringJobInstance = FakeJobsDataFactory::getRecurringJobDataInstance();

        $url = action('Jobs\RecurringJobController@store');

        $response = $this->postJson($url, $recurringJobInstance->toArray());
        $response->assertStatus(201);

        $jobId = $response->getData('id');
        $job   = RecurringJob::findOrFail($jobId);

        self::assertEquals($recurringJobInstance->getRecurrenceRule(), $job->recurrence_rule);
    }

    public function testDeleteRecord()
    {
        $recurringJob = factory(RecurringJob::class)->create();

        $url = action('Jobs\RecurringJobController@destroy', ['id' => $recurringJob->id]);
        $this->deleteJson($url)
            ->assertStatus(200);

        $reloaded = RecurringJob::find($recurringJob->id);
        self::assertNull($reloaded);
    }

    public function testGetOneRecord()
    {
        $recurringJob = factory(RecurringJob::class)->create();

        $url = action('Jobs\RecurringJobController@show', ['id' => $recurringJob->id]);

        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($recurringJob->id)
            ->assertSee($recurringJob->recurrence_rule)
            ->assertSee($recurringJob->insurer_id);
    }

    public function testGetAllRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 3);
        factory(RecurringJob::class, $countOfRecords)->create();

        $url = action('Jobs\RecurringJobController@index');

        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }
}
