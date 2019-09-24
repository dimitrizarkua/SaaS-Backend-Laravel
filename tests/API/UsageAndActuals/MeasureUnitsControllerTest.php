<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\UsageAndActuals\Models\MeasureUnit;
use Tests\API\ApiTestCase;

/**
 * Class MeasureUnitsControllerTest
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class MeasureUnitsControllerTest extends ApiTestCase
{
    protected $permissions = [
        'jobs.usage.view',
        'management.materials.measure_units',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            MeasureUnit::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(MeasureUnit::class, $numberOfRecords)->create();

        $url      = action('UsageAndActuals\MeasureUnitsController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $data = [
            'name' => $this->faker->title,
            'code' => $this->faker->postcode,
        ];

        $url      = action('UsageAndActuals\MeasureUnitsController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = MeasureUnit::findOrFail($modelId);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['code'], $model->code);
    }

    public function testShowMethod()
    {
        /** @var MeasureUnit $model */
        $model = factory(MeasureUnit::class)->create();

        $url = action('UsageAndActuals\MeasureUnitsController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['code'], $model->code);
    }

    public function testUpdateMethod()
    {
        /** @var MeasureUnit $model */
        $model = factory(MeasureUnit::class)->create();

        $url = action('UsageAndActuals\MeasureUnitsController@update', [
            'id' => $model->id,
        ]);

        $data = [
            'name' => $this->faker->title,
            'code' => $this->faker->postcode,
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = MeasureUnit::findOrFail($model->id);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['code'], $reloaded->code);
    }

    public function testDestroyMethod()
    {
        /** @var MeasureUnit $model */
        $model = factory(MeasureUnit::class)->create();

        $url = action('UsageAndActuals\MeasureUnitsController@destroy', [
            'id' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(MeasureUnit::find($model->id));
    }
}
