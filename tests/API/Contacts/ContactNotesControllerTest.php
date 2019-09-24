<?php

namespace Tests\API\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Notes\Models\Note;
use Tests\API\ApiTestCase;

/**
 * Class ContactNotesControllerTest
 *
 * @package Tests\API\Contacts
 * @group   contacts
 * @group   api
 */
class ContactNotesControllerTest extends ApiTestCase
{
    protected $permissions = ['contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete'];

    public function testGetAllRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 5);

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::PERSON,
        ]);
        $notes = factory(Note::class, $countOfRecords)->create();
        $contact->notes()->attach($notes);

        $url = action('Contacts\ContactNotesController@getContactNotes', [
            'contact_id' => $contact->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testGetAllCompanyRecords()
    {
        $countOfRecords = $this->faker->numberBetween(1, 2);

        /** @var Contact $contact */
        $contact = factory(Contact::class)->create([
            'contact_type' => ContactTypes::COMPANY,
        ]);
        $notes = factory(Note::class, $countOfRecords)->create();
        $contact->notes()->attach($notes);

        $url = action('Contacts\ContactNotesController@getContactNotes', [
            'contact_id' => $contact->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData()
            ->assertJsonDataCount($countOfRecords);
    }

    public function testGetOneRecord()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $note    = factory(Note::class)->create();
        $contact->notes()->attach($note);

        $url = action('Contacts\ContactNotesController@viewContactNote', [
            'contact_id' => $contact->id,
            'note_id'    => $note->id,
        ]);

        $response = $this->getJson($url);
        $response->assertStatus(200)
            ->assertSeeData();

        $data = $response->getData();
        self::assertArrayHasKey('contacts', $data);
        self::assertArrayHasKey('documents', $data);
    }

    public function testAddContactNote()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $note    = factory(Note::class)->create();

        $url = action('Contacts\ContactNotesController@addContactNote', [
            'contact_id' => $contact->id,
            'note_id'    => $note->id,
        ]);

        $response = $this->postJson($url);
        $response->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertNotNull($reloaded);
        self::assertCount(1, $reloaded->notes);
        self::assertEquals($note->id, $reloaded->notes[0]->id);
    }

    public function testDeleteContactNote()
    {
        /** @var Contact $contact */
        $contact = factory(Contact::class)->create();
        $note    = factory(Note::class)->create([
            'user_id' => $this->user->id
        ]);

        $contact->notes()->attach($note->id);

        $url = action('Contacts\ContactNotesController@deleteContactNote', [
            'contact_id' => $contact->id,
            'note_id'    => $note->id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        $reloaded = Contact::find($contact->id);
        self::assertFalse($reloaded->notes()->exists());
    }
}
