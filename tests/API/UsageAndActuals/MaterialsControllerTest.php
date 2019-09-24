<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\UsageAndActuals\Models\Material;
use App\Components\UsageAndActuals\Models\MeasureUnit;
use Tests\API\ApiTestCase;

/**
 * Class MaterialsControllerTest
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class MaterialsControllerTest extends ApiTestCase
{

    protected $permissions = [
        'jobs.usage.view',
        'management.materials',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            Material::class,
            MeasureUnit::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(Material::class, $numberOfRecords)->create();

        $url      = action('UsageAndActuals\MaterialsController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $data = [
            'name'                       => $this->faker->title,
            'measure_unit_id'            => factory(MeasureUnit::class)->create()->id,
            'default_sell_cost_per_unit' => $this->faker->randomFloat(2, 100, 200),
            'default_buy_cost_per_unit'  => $this->faker->randomFloat(2, 50, 100),
        ];

        $url      = action('UsageAndActuals\MaterialsController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = Material::findOrFail($modelId);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['measure_unit_id'], $model->measure_unit_id);
        self::assertEquals($data['default_sell_cost_per_unit'], $model->default_sell_cost_per_unit);
        self::assertEquals($data['default_buy_cost_per_unit'], $model->default_buy_cost_per_unit);
    }

    public function testShowMethod()
    {
        /** @var Material $model */
        $model = factory(Material::class)->create();

        $url = action('UsageAndActuals\MaterialsController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['measure_unit_id'], $model->measure_unit_id);
        self::assertEquals($data['default_sell_cost_per_unit'], $model->default_sell_cost_per_unit);
        self::assertEquals($data['default_buy_cost_per_unit'], $model->default_buy_cost_per_unit);
    }

    public function testUpdateMethod()
    {
        $model = factory(Material::class)->create();

        $url = action('UsageAndActuals\MaterialsController@update', [
            'id' => $model->id,
        ]);

        $data     = [
            'name'                       => $this->faker->title,
            'measure_unit_id'            => factory(MeasureUnit::class)->create()->id,
            'default_sell_cost_per_unit' => $this->faker->randomFloat(2, 100, 200),
            'default_buy_cost_per_unit'  => $this->faker->randomFloat(2, 50, 100),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = Material::findOrFail($model->id);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['measure_unit_id'], $reloaded->measure_unit_id);
        self::assertEquals($data['default_sell_cost_per_unit'], $reloaded->default_sell_cost_per_unit);
        self::assertEquals($data['default_buy_cost_per_unit'], $reloaded->default_buy_cost_per_unit);
    }

    public function testDestroyMethod()
    {
        /** @var Material $model */
        $model = factory(Material::class)->create();

        $url = action('UsageAndActuals\MaterialsController@destroy', [
            'id' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(Material::find($model->id));
    }
}
