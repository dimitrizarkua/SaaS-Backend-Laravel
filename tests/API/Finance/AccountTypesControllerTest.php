<?php

namespace Tests\API\Finance;

use App\Components\Finance\Models\AccountType;
use Tests\API\ApiTestCase;

/**
 * Class AccountTypesControllerTest
 *
 * @package Tests\API\Finance
 * @group   account-types
 * @group   finance
 */
class AccountTypesControllerTest extends ApiTestCase
{
    protected $permissions = ['finance.gl_accounts.manage'];

    public function setUp()
    {
        parent::setUp();
        $this->models[] = AccountType::class;
    }

    public function testIndexMethod()
    {
        $numberOfRecords = $this->faker->numberBetween(5, 9);
        factory(AccountType::class, $numberOfRecords)->create();

        $url      = action('Finance\AccountTypesController@index');
        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertJsonDataCount($numberOfRecords);
    }

    public function testCreateMethod()
    {
        $data = [
            'name'                     => $this->faker->name,
            'increase_action_is_debit' => $this->faker->boolean,
            'show_on_pl'               => $this->faker->boolean,
            'show_on_bs'               => $this->faker->boolean,
        ];

        $url      = action('Finance\AccountTypesController@store');
        $response = $this->postJson($url, $data);
        $response->assertStatus(201);

        $model = AccountType::where('name', $data['name'])
            ->first();

        self::assertNotNull($model);
        self::assertEquals($data['increase_action_is_debit'], $model->increase_action_is_debit);
        self::assertEquals($data['show_on_pl'], $model->show_on_pl);
        self::assertEquals($data['show_on_bs'], $model->show_on_bs);
    }

    public function testCreateMethodShouldReturnValidationErrorResponse()
    {
        $data = [];

        $url = action('Finance\AccountTypesController@store');
        $this->postJson($url, $data)
            ->assertStatus(422);
    }

    public function testShowMethod()
    {
        /** @var AccountType $model */
        $model = factory(AccountType::class)->create();
        $url   = action('Finance\AccountTypesController@show', [
            'id' => $model->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200);

        $data = $response->getData();
        self::assertEquals($data['id'], $model->id);
        self::assertEquals($data['name'], $model->name);
        self::assertEquals($data['increase_action_is_debit'], $model->increase_action_is_debit);
        self::assertEquals($data['show_on_pl'], $model->show_on_pl);
        self::assertEquals($data['show_on_bs'], $model->show_on_bs);
    }

    public function testShowMethodShouldReturnNotFoundResponse()
    {
        $url = action('Finance\AccountTypesController@show', [
            'id' => 0,
        ]);

        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testUpdateMethod()
    {
        /** @var AccountType $model */
        $model = factory(AccountType::class)->create();
        $data  = [
            'name' => $this->faker->word,
        ];

        $url      = action('Finance\AccountTypesController@update', [
            'id' => $model->id,
        ]);
        $response = $this->patchJson($url, $data);
        $response->assertStatus(200);

        $reloaded = AccountType::findOrFail($model->id);
        self::assertNotNull($reloaded);
        self::assertEquals($data['name'], $reloaded->name);
    }

    public function testUpdateMethodShouldReturnValidationErrorResponse()
    {
        /** @var AccountType $model */
        $model = factory(AccountType::class)->create();
        $data  = [
            'name' => $model->name,
        ];

        $url = action('Finance\AccountTypesController@update', [
            'id' => $model->id,
        ]);
        $this->patchJson($url, $data)
            ->assertStatus(422);
    }
}
