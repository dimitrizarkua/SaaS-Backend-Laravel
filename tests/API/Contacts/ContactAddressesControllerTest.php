<?php

namespace Tests\API\Contacts;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use Tests\API\ApiTestCase;

/**
 * Class ContactAddressesControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 * @group   api
 */
class ContactAddressesControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete'];

    public function testGetAllRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 5);

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $addresses = factory(Address::class, $countOfRecords)->create();
        $contact->addresses()->attach($addresses, [
            'type' => $this->faker->randomElement(AddressContactTypes::values())
        ]);

        $url = action('Contacts\ContactAddressesController@getContactAddresses', [
            'contact_id' => $contact->id
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testAddContactAddress()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $address = factory(Address::class)->create();

        $url = action('Contacts\ContactAddressesController@addContactAddress', [
            'contact_id' => $contact->id,
            'address_id' => $address->id,
        ]);

        $response = $this->postJson($url, [
            'type' => $this->faker->randomElement(AddressContactTypes::values())
        ]);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->addresses);
        self::assertEquals($reloaded->addresses[0]->id, $address->id);
    }

    public function testAddContactAddressValidationFail()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $address = factory(Address::class)->create();

        $url = action('Contacts\ContactAddressesController@addContactAddress', [
            'contact_id' => $contact->id,
            'address_id' => $address->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(422);

        $data = $response->json();
        self::assertNotNull($data);
        self::assertArrayHasKey('fields', $data);
        self::assertArrayHasKey('type', $data['fields']);
    }

    public function testDeleteContactAddress()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $address = factory(Address::class)->create();
        $contact->addresses()->attach($address, [
            'type' => $this->faker->randomElement(AddressContactTypes::values())
        ]);

        $url = action('Contacts\ContactAddressesController@deleteContactAddress', [
            'contact_id' => $contact->id,
            'address_id' => $address->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertNotNull($reloaded);
        self::assertCount(0, $reloaded->addresses);
    }
}
