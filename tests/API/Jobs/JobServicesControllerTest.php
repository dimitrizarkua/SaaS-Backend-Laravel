<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Models\JobService;
use Tests\API\ApiTestCase;

/**
 * Class JobServicesControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 */
class JobServicesControllerTest extends ApiTestCase
{
    protected $permissions = ['jobs.view', 'jobs.update'];

    public function testGetAllRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 5);
        factory(JobService::class, $countOfRecords)->create();

        $url = action('Jobs\JobServicesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testGetOneRecord()
    {
        $service = factory(JobService::class)->create();

        $url = action('Jobs\JobServicesController@show', [
            'service_id' => $service->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertEquals($data['id'], $service->id);
        self::assertEquals($data['name'], $service->name);
    }

    public function testAddJobService()
    {
        $url  = action('Jobs\JobServicesController@store');
        $data = [
            'name' => $this->faker->word,
        ];

        $response = $this->postJson($url, $data);
        $response->assertStatus(201)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = JobService::find($responseData['id']);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testUpdateJobService()
    {
        $service = factory(JobService::class)->create();

        $url  = action('Jobs\JobServicesController@update', [
            'service_id' => $service->id,
        ]);
        $data = [
            'name' => $this->faker->word,
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(200)
            ->assertSeeData();

        $responseData = $response->getData();
        $reloaded     = JobService::find($responseData['id']);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testDeleteJobService()
    {
        $service = factory(JobService::class)->create();

        $url = action('Jobs\JobServicesController@destroy', [
            'service_id' => $service->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = JobService::find($service->id);
        self::assertNull($reloaded);
    }
}
