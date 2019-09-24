<?php

namespace Tests\API\Messages;

use App\Components\Documents\Models\Document;
use App\Components\Messages\Enums\MessageParticipantTypes;
use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Models\DocumentMessage;
use App\Components\Messages\Models\Message;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\API\ApiTestCase;

/**
 * Class MessageControllerTest
 *
 * @package Tests\API\Messages
 * @group   messages
 * @group   api
 */
class MessageControllerTest extends ApiTestCase
{
    protected $permissions = [
        'messages.view',
        'messages.manage',
    ];

    /**
     * Creates new message with status draft.
     *
     * @param int|null $senderId Sender Id.
     *
     * @return \App\Components\Messages\Models\Message
     * @throws \Throwable
     */
    private function createDraftMessage(?int $senderId = null): Message
    {
        /** @var Message $instance */
        $instance = factory(Message::class)->create(['sender_user_id' => $senderId]);
        $instance->changeStatus(MessageStatuses::DRAFT);

        return $instance;
    }

    /**
     * @throws \Throwable
     */
    public function testGetOneRecord()
    {
        $instance = $this->createDraftMessage();

        $url = action('Messages\MessagesController@show', ['message_id' => $instance->id]);
        $response = $this->getJson($url);

        $response->assertStatus(200)
            ->assertSeeData()
            ->assertSee($instance->id)
            ->assertSee($instance->subject)
            ->assertSee($instance->message_body);
    }

    public function testNotFoundResponseWhenGettingNotExistingRecord()
    {
        $url = action('Messages\MessagesController@show', ['message_id' => 0]);
        $response = $this->getJson($url);

        $response->assertStatus(404);
    }

    public function testCreateRecord()
    {
        $url = action('Messages\MessagesController@store');
        $data = [
            'type'       => $this->faker->randomElement(MessageTypes::values()),
            'recipients' => [
                [
                    'type'    => MessageParticipantTypes::TO,
                    'address' => $this->faker->email,
                    'name'    => $this->faker->name,
                ],
            ],
            'subject'    => $this->faker->sentence,
            'body'       => $this->faker->paragraph,
        ];

        $response = $this->postJson($url, $data);

        $response->assertStatus(201)
            ->assertSeeData();

        $recordId = $response->getData()['id'];

        $instance = Message::findOrFail($recordId);
        self::assertEquals($this->user->id, $instance->sender_user_id);
        self::assertEquals($data['type'], $instance->message_type);
        self::assertEquals($data['subject'], $instance->subject);
        self::assertEquals($data['body'], $instance->message_body);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateRecord()
    {
        $instance = $this->createDraftMessage($this->user->id);

        $url = action('Messages\MessagesController@update', ['message_id' => $instance->id]);
        $data = [
            'type'       => $this->faker->randomElement(MessageTypes::values()),
            'recipients' => [
                [
                    'type'    => MessageParticipantTypes::TO,
                    'address' => $this->faker->email,
                    'name'    => $this->faker->name,
                ],
            ],
            'subject'    => $this->faker->sentence,
            'body'       => $this->faker->paragraph,
        ];

        $response = $this->patchJson($url, $data);

        $response->assertStatus(200);

        $instance = Message::findOrFail($instance->id);
        self::assertEquals($data['type'], $instance->message_type);
        self::assertEquals($data['subject'], $instance->subject);
        self::assertEquals($data['body'], $instance->message_body);
    }

    /**
     * @throws \Throwable
     */
    public function testOthersCantUpdateSomeonesRecord()
    {
        $instance = $this->createDraftMessage();

        $url = action('Messages\MessagesController@update', ['message_id' => $instance->id]);
        $data = [
            'type'       => $this->faker->randomElement(MessageTypes::values()),
            'recipients' => [
                [
                    'type'    => MessageParticipantTypes::TO,
                    'address' => $this->faker->email,
                    'name'    => $this->faker->name,
                ],
            ],
            'subject'    => $this->faker->sentence,
            'body'       => $this->faker->paragraph,
        ];

        $response = $this->patchJson($url, $data);

        $response->assertStatus(403);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteRecord()
    {
        $instance = $this->createDraftMessage($this->user->id);

        $url = action('Messages\MessagesController@destroy', ['message_id' => $instance->id]);
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        Message::findOrFail($instance->id);
    }

    /**
     * @throws \Throwable
     */
    public function testOthersCantDeleteSomeonesRecord()
    {
        $instance = $this->createDraftMessage();

        $url = action('Messages\MessagesController@destroy', ['message_id' => $instance->id]);
        $response = $this->deleteJson($url);

        $response->assertStatus(403);
    }

    /**
     * @throws \Throwable
     */
    public function testAttachDocumentToRecord()
    {
        $message = $this->createDraftMessage($this->user->id);

        /** @var Document $document */
        $document = factory(Document::class)->create();

        $url = action('Messages\MessagesController@attachDocument', [
            'message_id'  => $message->id,
            'document_id' => $document->id,
        ]);
        $response = $this->postJson($url);

        $response->assertStatus(200);

        DocumentMessage::query()->where([
            'document_id' => $document->id,
            'message_id'  => $message->id,
        ])->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testOthersCantAttachDocumentToRecord()
    {
        $message = $this->createDraftMessage();
        /** @var Document $document */
        $document = factory(Document::class)->create();

        $url = action('Messages\MessagesController@attachDocument', [
            'message_id'  => $message->id,
            'document_id' => $document->id,
        ]);
        $response = $this->postJson($url);

        $response->assertStatus(403);
    }

    /**
     * @throws \Throwable
     */
    public function testDetachDocumentFromRecord()
    {
        $message = $this->createDraftMessage($this->user->id);

        /** @var DocumentMessage $documentMessage */
        $documentMessage = factory(DocumentMessage::class)->create(['message_id' => $message->id]);

        $url = action('Messages\MessagesController@detachDocument', [
            'message_id'  => $documentMessage->message_id,
            'document_id' => $documentMessage->document_id,
        ]);
        $response = $this->deleteJson($url);

        $response->assertStatus(200);

        self::expectException(ModelNotFoundException::class);

        DocumentMessage::query()->where([
            'document_id' => $documentMessage->document_id,
            'message_id'  => $documentMessage->message_id,
        ])->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testOthersCantDetachDocumentFromRecord()
    {
        $message = $this->createDraftMessage();

        /** @var DocumentMessage $documentMessage */
        $documentMessage = factory(DocumentMessage::class)->create(['message_id' => $message->id]);

        $url = action('Messages\MessagesController@detachDocument', [
            'message_id'  => $documentMessage->message_id,
            'document_id' => $documentMessage->document_id,
        ]);
        /** @var \Tests\API\TestResponse $response */
        $response = $this->deleteJson($url);

        $response->assertStatus(403);
    }
}
