<?php

namespace Tests\API\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Tags\Models\Tag;
use Tests\API\ApiTestCase;

/**
 * Class ContactTagsControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 * @group   api
 */
class ContactTagsControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete'];

    public function testGetAllRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 5);

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $tags    = factory(Tag::class, $countOfRecords)->create();
        $contact->tags()->attach($tags);

        $url = action('Contacts\ContactTagsController@getContactTags', [
            'contact_id' => $contact->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testAddContactTag()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $tag     = factory(Tag::class)->create();

        $url = action('Contacts\ContactTagsController@addContactTag', [
            'contact_id' => $contact->id,
            'tag_id'     => $tag->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->tags);
        self::assertEquals($tag->id, $reloaded->tags[0]->id);
    }

    public function testDeleteContactTag()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $tag     = factory(Tag::class)->create();
        $contact->tags()->attach($tag);

        $url = action('Contacts\ContactTagsController@deleteContactTag', [
            'contact_id' => $contact->id,
            'tag_id'     => $tag->id,
        ]);

        $response = $this->deleteJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertNotNull($reloaded);
        self::assertCount(0, $reloaded->tags);
    }
}
