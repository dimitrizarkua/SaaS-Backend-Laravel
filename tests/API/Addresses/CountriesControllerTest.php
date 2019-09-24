<?php

namespace Tests\API\Addresses;

use App\Components\Addresses\Helpers\CountryHelper;
use App\Components\Addresses\Models\Country;
use Tests\API\ApiTestCase;

/**
 * Class CountriesControllerTest
 *
 * @package Tests\API\Addresses
 * @group   addresses
 * @group   api
 */
class CountriesControllerTest extends ApiTestCase
{
    protected $permissions = ['countries.view', 'countries.create', 'countries.delete'];

    public function testGetCountries()
    {
        $url = action('Addresses\CountriesController@index');

        $countOrRecords = $this->faker->numberBetween(5, 10);
        factory(Country::class, $countOrRecords)->create();

        $response = $this->json('GET', $url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetOneCountry()
    {
        /** @var Country $country */
        $country = factory(Country::class)->create();
        $url     = action('Addresses\CountriesController@show', ['id' => $country->id]);

        $response = $this->json('GET', $url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($country->name);
    }

    public function testGetOneCountryForNotExisting()
    {
        $url = action('Addresses\CountriesController@show', ['id' => $this->faker->randomDigit]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testCreateCountry()
    {
        $countryName = $this->faker->country;
        while (!CountryHelper::isCountryExists($countryName)) {
            $countryName = $this->faker->country;
        }

        $data = [
            'name'            => $countryName,
            'iso_alpha2_code' => CountryHelper::getAlpha2Code($countryName),
            'iso_alpha3_code' => CountryHelper::getAlpha3Code($countryName),
        ];


        $url      = action('Addresses\CountriesController@store');
        $response = $this->postJson($url, $data);

        $response->assertStatus(201);


        $country = Country::whereName($countryName)->first();
        self::assertNotNull($country);
        self::assertEquals($data['iso_alpha2_code'], $country->iso_alpha2_code);
        self::assertEquals($data['iso_alpha3_code'], $country->iso_alpha3_code);
    }

    public function testValidationErrorWhenCreatingCountry()
    {
        $url = action('Addresses\CountriesController@store');
        $this->postJson($url, [])
            ->assertStatus(422);
    }

    public function testDeleteCountry()
    {
        /** @var Country $country */
        $country   = factory(Country::class)->create();
        $countryId = $country->id;

        $url = action('Addresses\CountriesController@destroy', ['countryId' => $countryId]);
        $this->deleteJson($url)
            ->assertStatus(200);

        $reloaded = Country::find($countryId);
        self::assertNull($reloaded);
    }
}
