<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Locations\Models\Location;
use App\Components\UsageAndActuals\Enums\AllowanceTypeChargingIntervals;
use App\Components\UsageAndActuals\Models\AllowanceType;
use Tests\API\ApiTestCase;

/**
 * Class AllowanceTypesControllerTest
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class AllowanceTypesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'allowances.view',
        'management.jobs.allowances',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            AllowanceType::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(AllowanceType::class, $numberOfRecords)->create();

        $url      = action('UsageAndActuals\AllowanceTypesController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testStoreMethod()
    {
        $data = [
            'location_id'              => factory(Location::class)->create()->id,
            'name'                     => $this->faker->title,
            'charge_rate_per_interval' => $this->faker->randomFloat(2, 30, 40),
            'charging_interval'        => $this->faker->randomElement(AllowanceTypeChargingIntervals::values()),
        ];

        $url      = action('UsageAndActuals\AllowanceTypesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = AllowanceType::findOrFail($modelId);
        self::assertEquals($data['location_id'], $model->location_id);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['charge_rate_per_interval'], $model->charge_rate_per_interval);
        self::assertEquals($data['charging_interval'], $model->charging_interval);
    }

    public function testShowMethod()
    {
        /** @var AllowanceType $model */
        $model = factory(AllowanceType::class)->create();

        $url = action('UsageAndActuals\AllowanceTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['location_id'], $model->location_id);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['charge_rate_per_interval'], $model->charge_rate_per_interval);
        self::assertEquals($data['charging_interval'], $model->charging_interval);
    }

    public function testUpdateMethod()
    {
        /** @var AllowanceType $model */
        $model = factory(AllowanceType::class)->create();

        $url = action('UsageAndActuals\AllowanceTypesController@update', [
            'id' => $model->id,
        ]);

        $data     = [
            'location_id'              => factory(Location::class)->create()->id,
            'name'                     => $this->faker->title,
            'charge_rate_per_interval' => $this->faker->randomFloat(2, 30, 40),
            'charging_interval'        => $this->faker->randomElement(AllowanceTypeChargingIntervals::values()),
        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = AllowanceType::findOrFail($model->id);
        self::assertEquals($data['location_id'], $reloaded->location_id);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['charge_rate_per_interval'], $reloaded->charge_rate_per_interval);
        self::assertEquals($data['charging_interval'], $reloaded->charging_interval);
    }

    public function testDestroyMethod()
    {
        /** @var AllowanceType $model */
        $model = factory(AllowanceType::class)->create();

        $url = action('UsageAndActuals\AllowanceTypesController@destroy', [
            'id' => $model->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        self::assertNull(AllowanceType::find($model->id));
    }
}
