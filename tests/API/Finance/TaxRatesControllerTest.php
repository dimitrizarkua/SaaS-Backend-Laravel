<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\TaxRate;
use Tests\API\ApiTestCase;

/**
 * Class TaxRatesControllerTest
 *
 * @package Tests\API\Finance
 * @group   tax-rates
 * @group   finance
 */
class TaxRatesControllerTest extends ApiTestCase
{
    protected $permissions = ['finance.gl_accounts.manage'];

    public function setUp()
    {
        parent::setUp();
        $this->models[] = TaxRate::class;
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(TaxRate::class, $numberOfRecords)->create();

        $url      = action('Finance\TaxRatesController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testCreateMethod()
    {
        $data = [
            'name' => $this->faker->name,
            'rate' => $this->faker->randomFloat(2, 0, 1),
        ];

        $url = action('Finance\TaxRatesController@store');
        $this->postJson($url, $data)
            ->assertStatus(201);

        $model = TaxRate::where('name', $data['name'])->first();
        self::assertNotNull($model);
        self::assertEquals($data['rate'], $model->rate);
    }

    public function testShouldBeValidatoinErrorResponse()
    {
        $data     = [
            'rate' => 100,
        ];
        $url      = action('Finance\TaxRatesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var TaxRate $model */
        $model = factory(TaxRate::class)->create();
        $url   = action('Finance\TaxRatesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);
        $data = $response->getData();

        self::assertEquals($data['id'], $model->id);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['rate'], $model->rate);
    }

    public function testShowMethodShouldReturnNotFound()
    {
        $url = action('Finance\TaxRatesController@show', [
            'id' => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testUpdateMethod()
    {
        /** @var TaxRate $model */
        $model = factory(TaxRate::class)->create();
        $url   = action('Finance\TaxRatesController@update', [
            'id' => $model->id,
        ]);

        $data = [
            'rate' => $this->faker->randomFloat(2, 0, 1),
        ];

        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = TaxRate::findOrFail($model->id);
        self::assertNotNull($reloaded);
        self::assertEquals($model->name, $reloaded->name);
        self::assertEquals($data['rate'], $reloaded->rate);
    }

    public function testUpdateMethodShouldReturnValidationError()
    {
        /** @var TaxRate $model */
        $model = factory(TaxRate::class)->create();
        $url   = action('Finance\TaxRatesController@update', [
            'id' => $model->id,
        ]);

        $data = [
            'rate' => 100,
        ];

        $this->patchJson($url, $data)
            ->assertStatus(422);
    }
}
