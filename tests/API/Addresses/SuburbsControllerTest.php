<?php

namespace Tests\API\Addresses;

use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class SuburbsControllerTest
 *
 * @package Tests\API\Addresses
 * @group   addresses
 * @group   api
 */
class SuburbsControllerTest extends ApiTestCase
{
    protected $permissions = ['suburbs.view', 'suburbs.create', 'suburbs.update', 'suburbs.delete'];

    public function testGetSuburbs()
    {
        $countOrRecords = $this->faker->numberBetween(5, 10);
        factory(Suburb::class, $countOrRecords)->create();
        $url = action('Addresses\SuburbsController@index');

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetSuburbsWithFiltrationByState()
    {
        /** @var State $state */
        $state          = factory(State::class)->create();
        $countOrRecords = $this->faker->numberBetween(5, 10);
        factory(Suburb::class, $countOrRecords)->create([
            'state_id' => $state->id,
        ]);
        factory(Suburb::class, $countOrRecords)->create();
        $url = action('Addresses\SuburbsController@index', ['state_id' => $state->id]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetSuburbsWithFiltrationByCountry()
    {
        /** @var Country $countryOne */
        $countryOne = factory(Country::class)->create();
        /** @var Country $countryTwo */
        $countryTwo = factory(Country::class)->create();
        /** @var State $stateOne */
        $stateOne = factory(State::class)->create([
            'country_id' => $countryOne->id,
        ]);
        /** @var State $stateTwo */
        $stateTwo = factory(State::class)->create([
            'country_id' => $countryTwo->id,
        ]);

        $countOrRecordsForCountryOne = $this->faker->numberBetween(5, 10);
        $countOrRecordsForCountryTwo = $this->faker->numberBetween(5, 10);

        factory(Suburb::class, $countOrRecordsForCountryOne)->create([
            'state_id' => $stateOne->id,
        ]);
        factory(Suburb::class, $countOrRecordsForCountryTwo)->create([
            'state_id' => $stateTwo->id,
        ]);

        $url = action('Addresses\SuburbsController@index', [
            'country_id' => $countryOne->id,
        ]);

        $this->getJson($url)->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecordsForCountryOne);
    }

    public function testGetOneSuburb()
    {
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();
        $url    = action('Addresses\SuburbsController@show', ['id' => $suburb->id]);

        $response = $this->json('GET', $url);
        $response->assertStatus(200)
            ->assertSeeData();
        $data = $response->getData();

        self::compareDataWithModel($data, $suburb);
    }

    public function testGetOneSuburbForNotExisting()
    {
        $url = action('Addresses\SuburbsController@show', ['id' => $this->faker->randomDigit]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testCreateSuburb()
    {
        /** @var State $state */
        $state   = factory(State::class)->create();
        $request = [
            'state_id' => $state->id,
            'name'     => $this->faker->city,
            'postcode' => $this->faker->postcode,
        ];
        $url     = action('Addresses\SuburbsController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201);
        $data     = $response->getData();

        Suburb::findOrFail($data['id']);
        self::assertEquals($data['state_id'], $request['state_id']);
        self::assertEquals($data['name'], $request['name']);
        self::assertEquals($data['postcode'], $request['postcode']);
    }

    public function testValidationErrorWhenCreatingSuburb()
    {
        $url = action('Addresses\SuburbsController@store');
        $this->postJson($url, [])
            ->assertStatus(422);
    }

    public function testUpdateSuburb()
    {
        /** @var State $state */
        $state = factory(State::class)->create();
        /** @var Suburb $suburb */
        $suburb  = factory(Suburb::class)->create();
        $request = [
            'state_id' => $state->id,
            'name'     => $this->faker->city,
            'postcode' => $this->faker->postcode,
        ];
        $url     = action('Addresses\SuburbsController@update', ['id' => $suburb->id]);

        $response       = $this->patchJson($url, $request)
            ->assertStatus(200);
        $data           = $response->getData();
        $reloadedSuburb = Suburb::findOrFail($suburb->id);

        self::compareDataWithModel($request, $reloadedSuburb);
        self::assertEquals($request['state_id'], $data['state_id']);
        self::assertEquals($request['name'], $data['name']);
        self::assertEquals($request['postcode'], $data['postcode']);
    }


    public function testDeleteSuburb()
    {
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();

        $url = action('Addresses\SuburbsController@destroy', ['id' => $suburb->id]);
        $this->deleteJson($url)
            ->assertStatus(200);

        $this->expectException(ModelNotFoundException::class);
        Suburb::findOrFail($suburb->id);
    }
}
