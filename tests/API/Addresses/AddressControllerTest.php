<?php

namespace Tests\API\Addresses;

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class AddressControllerTest
 *
 * @package Tests\API\Addresses
 * @group   addresses
 * @group   api
 */
class AddressControllerTest extends ApiTestCase
{
    protected $permissions = ['addresses.view', 'addresses.create', 'addresses.update', 'addresses.delete'];

    public function testGetAddresses()
    {
        $url = action('Addresses\AddressController@index');

        $countOrRecords = $this->faker->numberBetween(5, 10);
        factory(Address::class, $countOrRecords)->create();

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetAddressesWithFiltrationBySuburb()
    {
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();

        $countOrRecords = $this->faker->numberBetween(5, 10);

        factory(Address::class, $countOrRecords)->create([
            'suburb_id' => $suburb->id,
        ]);
        factory(Address::class, $countOrRecords)->create();

        $url = action('Addresses\AddressController@index', ['suburb_id' => $suburb->id]);
        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecords);
    }

    public function testGetAddressesWithFiltrationByState()
    {
        /** @var State $stateOne */
        $stateOne = factory(State::class)->create();
        /** @var State $stateTwo */
        $stateTwo = factory(State::class)->create();
        /** @var Suburb $suburbOne */
        $suburbOne = factory(Suburb::class)->create([
            'state_id' => $stateOne->id,
        ]);
        /** @var Suburb $suburbTwo */
        $suburbTwo = factory(Suburb::class)->create([
            'state_id' => $stateTwo->id,
        ]);

        $countOrRecordsForStateOne = $this->faker->numberBetween(5, 10);
        $countOrRecordsForStateTwo = $this->faker->numberBetween(5, 10);

        factory(Address::class, $countOrRecordsForStateOne)->create([
            'suburb_id' => $suburbOne->id,
        ]);
        factory(Address::class, $countOrRecordsForStateTwo)->create([
            'suburb_id' => $suburbTwo->id,
        ]);

        $url = action('Addresses\AddressController@index', [
            'state_id' => $stateOne->id,
        ]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertSeeData()
            ->assertSeePagination()
            ->assertJsonDataCount($countOrRecordsForStateOne);
    }


    public function testGetOneAddress()
    {
        /** @var Address $address */
        $address = factory(Address::class)->create();
        $url     = action('Addresses\AddressController@show', ['id' => $address->id]);

        $response = $this->json('GET', $url);
        $response->assertStatus(200)
            ->assertSeeData();
        $data = $response->getData();

        self::compareDataWithModel($data, $address);
    }

    public function testGetOneAddressForNotExisting()
    {
        $url = action('Addresses\AddressController@show', ['id' => $this->faker->randomDigit]);
        $this->getJson($url)
            ->assertStatus(404);
    }

    public function testCreateAddress()
    {
        /** @var Suburb $suburb */
        $suburb  = factory(Suburb::class)->create();
        $request = [
            'contact_name'   => $this->faker->name,
            'contact_phone'  => $this->faker->phoneNumber,
            'suburb_id'      => $suburb->id,
            'address_line_1' => $this->faker->address,
            'address_line_2' => $this->faker->address,
        ];
        $url     = action('Addresses\AddressController@store');

        $response = $this->postJson($url, $request)
            ->assertStatus(201);
        $data     = $response->getData();

        Address::findOrFail($data['id']);
        self::assertEquals($request['contact_name'], $data['contact_name']);
        self::assertEquals($request['contact_phone'], $data['contact_phone']);
        self::assertEquals($request['suburb_id'], $data['suburb_id']);
        self::assertEquals($request['address_line_1'], $data['address_line_1']);
        self::assertEquals($request['address_line_2'], $data['address_line_2']);
    }

    public function testValidationErrorWhenCreatingAddress()
    {
        $url = action('Addresses\SuburbsController@store');
        $this->postJson($url)
            ->assertStatus(422);
    }

    public function testUpdateAddress()
    {
        /** @var Suburb $suburb */
        $suburb = factory(Suburb::class)->create();
        /** @var Address $address */
        $address = factory(Address::class)->create();
        $request = [
            'contact_name'   => $this->faker->name,
            'contact_phone'  => $this->faker->phoneNumber,
            'suburb_id'      => $suburb->id,
            'address_line_1' => $this->faker->address,
            'address_line_2' => $this->faker->address,
        ];
        $url     = action('Addresses\AddressController@update', ['id' => $address->id]);

        $response        = $this->patchJson($url, $request)
            ->assertStatus(200);
        $data            = $response->getData();
        $reloadedAddress = Address::findOrFail($address->id);

        self::compareDataWithModel($request, $reloadedAddress);
        self::assertEquals($request['contact_name'], $data['contact_name']);
        self::assertEquals($request['contact_phone'], $data['contact_phone']);
        self::assertEquals($request['suburb_id'], $data['suburb']['id']);
        self::assertEquals($request['address_line_1'], $data['address_line_1']);
        self::assertEquals($request['address_line_2'], $data['address_line_2']);
    }


    public function testDeleteAddress()
    {
        /** @var Address $address */
        $address = factory(Address::class)->create();
        $url     = action('Addresses\AddressController@destroy', ['id' => $address->id]);

        $this->deleteJson($url)
            ->assertStatus(200);

        $this->expectException(ModelNotFoundException::class);
        Address::findOrFail($address->id);
    }
}
