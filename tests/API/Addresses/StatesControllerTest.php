<?php

namespace Tests\API\Addresses;

use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class StatesControllerTest
 *
 * @package Tests\API\Addresses
 * @group   addresses
 * @group   api
 */
class StatesControllerTest extends ApiTestCase
{
    protected $permissions = ['states.view', 'states.create', 'states.update', 'states.delete'];

    public function testGetStates()
    {
        $countOrRecords = $this->faker->numberBetween(5, 10);
        factory(State::class, $countOrRecords)->create();
        $url = action('Addresses\StatesController@index');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetStatesWithFiltration()
    {
        /** @var Country $country */
        $country        = factory(Country::class)->create();
        $countOrRecords = $this->faker->numberBetween(5, 10);
        factory(State::class, $countOrRecords)->create([
            'country_id' => $country->id,
        ]);
        factory(State::class, $countOrRecords)->create();
        $url = action('Addresses\StatesController@index', ['country_id' => $country->id]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetOneState()
    {
        /** @var State $state */
        $state = factory(State::class)->create();
        $url   = action('Addresses\StatesController@show', ['id' => $state->id]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();
        $data = $response->getData();

        self::compareDataWithModel($data, $state);
    }

    public function testGetOneStateForNotExisting()
    {
        $url = action('Addresses\StatesController@show', ['id' => $this->faker->randomDigit]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testCreateState()
    {
        /** @var Country $country */
        $country = factory(Country::class)->create();
        $request = [
            'country_id' => $country->id,
            'name'       => $this->faker->word,
            'code'       => $this->faker->word,
        ];
        $url     = action('Addresses\StatesController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201);
        $data     = $response->getData();

        State::findOrFail($data['id']);
        self::assertEquals($request['country_id'], $data['country_id']);
        self::assertEquals($request['name'], $data['name']);
        self::assertEquals($request['code'], $data['code']);
    }

    public function testValidationErrorWhenCreatingState()
    {
        $url = action('Addresses\StatesController@store');
        $this->postJson($url)
            ->assertStatus(422);
    }

    public function testUpdateState()
    {
        /** @var Country $country */
        $country = factory(Country::class)->create();
        /** @var State $state */
        $state   = factory(State::class)->create();
        $request = [
            'country_id' => $country->id,
            'name'       => $this->faker->word,
            'code'       => $this->faker->word,
        ];
        $url     = action('Addresses\StatesController@update', ['id' => $state->id]);

        $response      = $this->patchJson($url, $request)
            ->assertStatus(200);
        $data          = $response->getData();
        $reloadedState = State::findOrFail($state->id);

        self::compareDataWithModel($request, $reloadedState);
        self::assertEquals($request['country_id'], $data['country_id']);
        self::assertEquals($request['name'], $data['name']);
        self::assertEquals($request['code'], $data['code']);
    }

    public function testDeleteState()
    {
        /** @var State $state */
        $state = factory(State::class)->create();

        $url = action('Addresses\StatesController@destroy', ['id' => $state->id]);
        $this->deleteJson($url)
            ->assertStatus(200);

        $this->expectException(ModelNotFoundException::class);
        State::findOrFail($state->id);
    }
}
