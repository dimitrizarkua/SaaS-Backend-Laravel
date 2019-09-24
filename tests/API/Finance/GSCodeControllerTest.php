<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\GSCode;
use Tests\API\ApiTestCase;

/**
 * Class GSCodeControllerTest
 *
 * @package Tests\API\Finance
 * @group   gs-codes
 * @group   finance
 */
class GSCodeControllerTest extends ApiTestCase
{
    protected $permissions = [
        'finance.gs_codes.manage',
        'finance.gs_codes.view',
    ];

    public function setUp()
    {
        parent::setUp();
        $models       = [
            GSCode::class,
        ];
        $this->models = array_merge($models, $this->models);
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(GSCode::class, $numberOfRecords)->create();

        $url      = action('Finance\GSCodesController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testCreateMethod()
    {
        $isBuyValue = $this->faker->boolean;
        $data       = [
            'name'        => $this->faker->word,
            'description' => $this->faker->word,
            'is_buy'      => $isBuyValue,
            'is_sell'     => !$isBuyValue,
        ];

        $url      = action('Finance\GSCodesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $modelId = $response->getData('id');
        $model   = GSCode::findOrFail($modelId);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['is_buy'], $model->is_buy);
        self::assertEquals($data['is_sell'], $model->is_sell);
    }

    public function testCreateMethodShouldReturnValidationError()
    {
        $data = [];
        $url  = action('Finance\GSCodesController@store');
        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var GSCode $model */
        $model = factory(GSCode::class)->create();

        $url = action('Finance\GSCodesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['description'], $model->description);
        self::assertEquals($data['is_buy'], $model->is_buy);
        self::assertEquals($data['is_sell'], $model->is_sell);
    }

    public function testShowMethodShouldReturnNotFoundError()
    {
        $url = action('Finance\GSCodesController@show', [
            'id' => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testUpdateMethod()
    {
        /** @var GSCode $model */
        $model = factory(GSCode::class)->create();

        $url = action('Finance\GSCodesController@update', [
            'id' => $model->id,
        ]);

        $data     = [
            'name'        => $this->faker->word . ' new_name_value',
            'description' => $this->faker->word . ' new_name_value',
            'is_buy'      => false,

        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);
        $reloaded = GSCode::findOrFail($model->id);
        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['is_buy'], $reloaded->is_buy);
    }

    public function testUpdateMethodIsBuyAndIsSellHaveEqualsValue()
    {
        /** @var GSCode $model */
        $model = factory(GSCode::class)->create();

        $url = action('Finance\GSCodesController@update', [
            'id' => $model->id,
        ]);

        $data     = [
            'name'        => $this->faker->word . ' new_name_value',
            'description' => $this->faker->word . ' new_name_value',
            'is_sell'     => false,
            'is_buy'      => false,

        ];
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);
        $reloaded = GSCode::findOrFail($model->id);

        self::assertEquals($data['name'], $reloaded->name);
        self::assertEquals($data['is_buy'], $reloaded->is_buy);
        self::assertNotEquals($data['is_sell'], $reloaded->is_sell);
        self::assertTrue($reloaded->is_sell);
    }

    public function testUpdateMethodShouldReturnValidationError()
    {
        /** @var GSCode $model */
        $model = factory(GSCode::class)->create();
        $url   = action('Finance\GSCodesController@update', [
            'id' => $model->id,
        ]);

        $data = [
            'is_buy'  => false,
            'is_sell' => true,
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(422);
    }
}
