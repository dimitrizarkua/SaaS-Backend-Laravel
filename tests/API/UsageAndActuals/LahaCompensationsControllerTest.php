<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\UsageAndActuals\Models\LahaCompensation;
use Tests\API\ApiTestCase;

/**
 * Class LahaCompensationsControllerTest
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class LahaCompensationsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'laha.view',
        'management.jobs.laha',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            LahaCompensation::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(LahaCompensation::class, $numberOfRecords)->create();

        $url      = action('UsageAndActuals\LahaCompensationsController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $data = [
            'rate_per_day' => $this->faker->randomFloat(2, 30, 40),
        ];

        $url      = action('UsageAndActuals\LahaCompensationsController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = LahaCompensation::findOrFail($modelId);
        self::assertEquals($data['rate_per_day'], $model->rate_per_day);
    }

    public function testShowMethod()
    {
        /** @var LahaCompensation $model */
        $model = factory(LahaCompensation::class)->create();

        $url = action('UsageAndActuals\LahaCompensationsController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['rate_per_day'], $model->rate_per_day);
    }

    public function testUpdateMethod()
    {
        /** @var LahaCompensation $model */
        $model = factory(LahaCompensation::class)->create();

        $url = action('UsageAndActuals\LahaCompensationsController@update', [
            'id' => $model->id,
        ]);

        $data     = [
            'rate_per_day' => $this->faker->randomFloat(2, 30, 40),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = LahaCompensation::findOrFail($model->id);
        self::assertEquals($data['rate_per_day'], $reloaded->rate_per_day);
    }

    public function testDestroyMethod()
    {
        /** @var LahaCompensation $model */
        $model = factory(LahaCompensation::class)->create();

        $url = action('UsageAndActuals\LahaCompensationsController@destroy', [
            'id' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(LahaCompensation::find($model->id));
    }
}
