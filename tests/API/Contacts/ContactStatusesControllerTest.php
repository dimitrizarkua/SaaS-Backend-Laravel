<?php

namespace Tests\API\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Http\Responses\Contacts\ContactStatusListResponse;
use Tests\API\ApiTestCase;

/**
 * Class ContactStatusesControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 * @group   api
 */
class ContactStatusesControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete'];

    public function testGetAllRecords()
    {
        $url = action('Contacts\ContactStatusesController@index');

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertValidSchema(ContactStatusListResponse::class, true)
            ->assertSeeData()
            ->assertJsonDataCount(ContactStatuses::count());
    }

    public function testChangeStatus22()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();

        $url = action('Contacts\ContactStatusesController@changeStatus', ['contact' => $contact->id]);

        $expectedStatus = $this->faker->randomElement(ContactStatuses::values());
        $response       = $this->patchJson($url, [
            'status' => $expectedStatus
        ]);

        $contact->refresh();
        $response->assertStatus(200);

        self::assertEquals($expectedStatus, $contact->latestStatus->status);
    }
}
