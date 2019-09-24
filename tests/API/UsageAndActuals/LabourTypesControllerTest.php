<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\UsageAndActuals\Models\LabourType;
use Tests\API\ApiTestCase;

/**
 * Class LabourTypesControllerTest
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class LabourTypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'labour.view',
        'management.jobs.labour',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            LabourType::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(LabourType::class, $numberOfRecords)->create();

        $url      = action('UsageAndActuals\LabourTypesController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $data = [
            'name'                    => $this->faker->title,
            'first_tier_hourly_rate'  => $this->faker->randomFloat(2, 30, 40),
            'second_tier_hourly_rate' => $this->faker->randomFloat(2, 50, 80),
            'third_tier_hourly_rate'  => $this->faker->randomFloat(2, 100, 130),
            'fourth_tier_hourly_rate' => $this->faker->randomFloat(2, 150, 200),
        ];

        $url      = action('UsageAndActuals\LabourTypesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = LabourType::findOrFail($modelId);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['first_tier_hourly_rate'], $model->first_tier_hourly_rate);
        self::assertEquals($data['second_tier_hourly_rate'], $model->second_tier_hourly_rate);
        self::assertEquals($data['third_tier_hourly_rate'], $model->third_tier_hourly_rate);
        self::assertEquals($data['fourth_tier_hourly_rate'], $model->fourth_tier_hourly_rate);
    }

    public function testShowMethod()
    {
        /** @var LabourType $model */
        $model = factory(LabourType::class)->create();

        $url = action('UsageAndActuals\LabourTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['first_tier_hourly_rate'], $model->first_tier_hourly_rate);
        self::assertEquals($data['second_tier_hourly_rate'], $model->second_tier_hourly_rate);
        self::assertEquals($data['third_tier_hourly_rate'], $model->third_tier_hourly_rate);
        self::assertEquals($data['fourth_tier_hourly_rate'], $model->fourth_tier_hourly_rate);
    }

    public function testUpdateMethod()
    {
        /** @var LabourType $model */
        $model = factory(LabourType::class)->create();

        $url = action('UsageAndActuals\LabourTypesController@update', [
            'id' => $model->id,
        ]);

        $data     = [
            'name'                    => $this->faker->title,
            'first_tier_hourly_rate'  => $this->faker->randomFloat(2, 30, 40),
            'second_tier_hourly_rate' => $this->faker->randomFloat(2, 50, 80),
            'third_tier_hourly_rate'  => $this->faker->randomFloat(2, 100, 130),
            'fourth_tier_hourly_rate' => $this->faker->randomFloat(2, 150, 200),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = LabourType::findOrFail($model->id);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['first_tier_hourly_rate'], $reloaded->first_tier_hourly_rate);
        self::assertEquals($data['second_tier_hourly_rate'], $reloaded->second_tier_hourly_rate);
        self::assertEquals($data['third_tier_hourly_rate'], $reloaded->third_tier_hourly_rate);
        self::assertEquals($data['fourth_tier_hourly_rate'], $reloaded->fourth_tier_hourly_rate);
    }

    public function testDestroyMethod()
    {
        /** @var LabourType $model */
        $model = factory(LabourType::class)->create();

        $url = action('UsageAndActuals\LabourTypesController@destroy', [
            'id' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(LabourType::find($model->id));
    }
}
