<?php

namespace Tests\API\UsageAndActuals;

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use Tests\API\ApiTestCase;

/**
 * Class EquipmentCategoriesControllerTest
 *
 * @package Tests\API\UsageAndActuals
 * @group   api
 * @group   equipment
 * @group   usage-and-actuals
 */
class EquipmentCategoriesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'equipment.view',
        'management.equipment',
    ];

    public function setUp()
    {
        parent::setUp();

        $models       = [
            EquipmentCategory::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $count = $this->faker->numberBetween(1, 5);
        factory(EquipmentCategory::class, $count)->create();
        $url = action('UsageAndActuals\EquipmentCategoriesController@index');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($count);
    }

    public function testCreateMethod()
    {
        $chargingInterval = $this->faker->randomElement(EquipmentCategoryChargingIntervals::values());
        $data             = [
            'name'                          => $this->faker->unique()->sentence(2),
            'is_airmover'                   => $this->faker->boolean,
            'is_dehum'                      => $this->faker->boolean,
            'default_buy_cost_per_interval' => $this->faker->randomFloat(2, 1, 1000),
            'charging_rate_per_interval'    => $this->faker->randomFloat(2, 1, 1000),
            'charging_interval'             => $chargingInterval,
        ];
        $url              = action('UsageAndActuals\EquipmentCategoriesController@store');

        $response = $this->postJson($url, $data)
            ->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = EquipmentCategory::findOrFail($modelId);

        self::assertEquals($model->name, $data['name']);
        self::assertEquals($model->is_airmover, $data['is_airmover']);
        self::assertEquals($model->is_dehum, $data['is_dehum']);
        self::assertEquals($model->default_buy_cost_per_interval, $data['default_buy_cost_per_interval']);
    }

    public function testCreateMethodReturnsValidationErrorWhenWrongRequest()
    {
        $data = [
            'name'                          => $this->faker->randomNumber(),
            'is_airmover'                   => $this->faker->word,
            'is_dehum'                      => $this->faker->word,
            'default_buy_cost_per_interval' => $this->faker->word,
            'charging_rate_per_interval'    => $this->faker->word,
            'charging_interval'             => $this->faker->randomNumber(),
        ];
        $url  = action('UsageAndActuals\EquipmentCategoriesController@store');

        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var EquipmentCategory $model */
        $model = factory(EquipmentCategory::class)->create();
        $url   = action('UsageAndActuals\EquipmentCategoriesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url)
            ->assertStatus(200);
        $data     = $response->getData();

        self::assertEquals($model->name, $data['name']);
        self::assertEquals($model->is_airmover, $data['is_airmover']);
        self::assertEquals($model->is_dehum, $data['is_dehum']);
        self::assertEquals($model->default_buy_cost_per_interval, $data['default_buy_cost_per_interval']);
    }

    public function testUpdateMethod()
    {
        /** @var EquipmentCategory $equipmentCategory */
        $equipmentCategory = factory(EquipmentCategory::class)->create();
        $data              = [
            'name'                          => $this->faker->unique()->sentence(2),
            'is_airmover'                   => $this->faker->boolean,
            'is_dehum'                      => $this->faker->boolean,
            'default_buy_cost_per_interval' => $this->faker->randomFloat(2, 1, 1000),
        ];
        $url               = action('UsageAndActuals\EquipmentCategoriesController@update', [
            'id' => $equipmentCategory->id,
        ]);

        $response = $this->patchJson($url, $data)
            ->assertStatus(200);

        $modelId = $response->getData('id');
        $model   = EquipmentCategory::findOrFail($modelId);

        self::assertEquals($model->name, $data['name']);
        self::assertEquals($model->is_airmover, $data['is_airmover']);
        self::assertEquals($model->is_dehum, $data['is_dehum']);
        self::assertEquals($model->default_buy_cost_per_interval, $data['default_buy_cost_per_interval']);
    }

    public function testUpdateMethodReturnsValidationErrorWhenWrongRequest()
    {
        /** @var EquipmentCategory $equipmentCategory */
        $equipmentCategory = factory(EquipmentCategory::class)->create();
        $data              = [
            'name'                          => $this->faker->randomNumber(),
            'is_airmover'                   => $this->faker->word,
            'is_dehum'                      => $this->faker->word,
            'default_buy_cost_per_interval' => $this->faker->word,
            'charging_rate_per_interval'    => $this->faker->word,
            'charging_interval'             => $this->faker->randomNumber(),
        ];
        $url               = action('UsageAndActuals\EquipmentCategoriesController@update', [
            'id' => $equipmentCategory->id,
        ]);

        $this->patchJson($url, $data)
            ->assertStatus(422);
    }

    public function testDestroyMethod()
    {
        /** @var EquipmentCategory $model */
        $model = factory(EquipmentCategory::class)->create();
        $url   = action('UsageAndActuals\EquipmentCategoriesController@destroy', [
            'id' => $model->id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(200);

        self::assertNull(EquipmentCategory::find($model->id));
    }
}
