<?php

namespace Tests\API\Notes;

use App\Components\Documents\Models\Document;
use App\Components\Notes\Models\DocumentNote;
use App\Components\Notes\Models\Note;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class NotesControllerTest
 *
 * @package Tests\API\Notes
 * @group   notes
 * @group   api
 */
class NotesControllerTest extends ApiTestCase
{
    protected $permissions = [
        'notes.view',
        'notes.create',
        'notes.update',
        'notes.delete',
    ];

    public function testGetOneRecord()
    {
        /** @var Note $instance */
        $instance = factory(Note::class)->create();

        $url = action('Notes\NotesController@show', ['note_id' => $instance->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($instance->id)
            ->assertSee($instance->note)
            ->assertSee($instance->user_id)
            ->assertSee($instance->documents);
    }

    public function testNotFoundResponseWhenGettingNotExistingRecord()
    {
        $url = action('Notes\NotesController@show', ['note_id' => 0]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->getJson($url);

        $response->assertStatus(404);
    }

    public function testCreateRecord()
    {
        $url  = action('Notes\NotesController@store');
        $data = ['note' => $this->faker->sentence,];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url, $data);

        $response->assertStatus(201)
            ->assertSeeData()
            ->assertSee($data['note']);

        $recordId = $response->getData()['id'];

        $instance = Note::findOrFail($recordId);
        self::assertEquals($this->user->id, $instance->user_id);
        self::assertEquals($data['note'], $instance->note);
    }

    public function testUpdateRecord()
    {
        /** @var Note $instance */
        $instance = factory(Note::class)->create(['user_id' => $this->user->id]);

        $url  = action('Notes\NotesController@update', ['note_id' => $instance->id]);
        $data = ['note' => $this->faker->sentence,];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(200);

        $instance = Note::findOrFail($instance->id);
        self::assertEquals($data['note'], $instance->note);
    }

    public function testOthersCantUpdateSomeonesRecord()
    {
        /** @var Note $instance */
        $instance = factory(Note::class)->create();

        $url  = action('Notes\NotesController@update', ['note_id' => $instance->id]);
        $data = ['note' => $this->faker->sentence,];

        /** @var \Tests\API\TestResponse $response */
        $response = $this->patchJson($url, $data);

        $response->assertStatus(403);
    }

    public function testDeleteRecord()
    {
        /** @var Note $instance */
        $instance = factory(Note::class)->create(['user_id' => $this->user->id]);

        $url = action('Notes\NotesController@destroy', ['note_id' => $instance->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        Note::findOrFail($instance->id);
    }

    public function testOthersCantDeleteSomeonesRecord()
    {
        /** @var Note $instance */
        $instance = factory(Note::class)->create();

        $url = action('Notes\NotesController@destroy', ['note_id' => $instance->id]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(403);
    }

    public function testAttachDocumentToRecord()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);
        /** @var Document $document */
        $document = factory(Document::class)->create();

        $url = action('Notes\NotesController@attachDocument', [
            'note_id'     => $note->id,
            'document_id' => $document->id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(200);

        DocumentNote::query()->where([
            'document_id' => $document->id,
            'note_id'     => $note->id,
        ])->firstOrFail();
    }

    public function testOthersCantAttachDocumentToRecord()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create();
        /** @var Document $document */
        $document = factory(Document::class)->create();

        $url = action('Notes\NotesController@attachDocument', [
            'note_id'     => $note->id,
            'document_id' => $document->id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->postJson($url);

        $response->assertStatus(403);
    }

    public function testDetachDocumentFromRecord()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create(['user_id' => $this->user->id]);

        /** @var DocumentNote $documentNote */
        $documentNote = factory(DocumentNote::class)->create(['note_id' => $note->id]);

        $url = action('Notes\NotesController@detachDocument', [
            'note_id'     => $documentNote->note_id,
            'document_id' => $documentNote->document_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        DocumentNote::query()->where([
            'document_id' => $documentNote->document_id,
            'note_id'     => $documentNote->note_id,
        ])->firstOrFail();
    }

    public function testOthersCantDetachDocumentFromRecord()
    {
        /** @var Note $note */
        $note = factory(Note::class)->create();

        /** @var DocumentNote $documentNote */
        $documentNote = factory(DocumentNote::class)->create(['note_id' => $note->id]);

        $url = action('Notes\NotesController@detachDocument', [
            'note_id'     => $documentNote->note_id,
            'document_id' => $documentNote->document_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(403);
    }
}
