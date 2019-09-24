<?php

namespace Tests\Unit\Messaging;

use App\Components\Documents\Models\Document;
use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Exceptions\NotAllowedException;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Jobs\Messages\DeliverMessage;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Class MessagingServiceTest
 *
 * @package Tests\Unit\MessagingServiceTest
 */
class MessagingServiceTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \App\Components\Messages\Services\MessagingService
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()->make(MessagingServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateEmailMessageWithoutForwardForDelivery()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);
        self::assertEquals($createdMessage->sender_user_id, $emailMessage->getSenderId());
        self::assertEquals($createdMessage->subject, $emailMessage->getSubject());
        self::assertEquals($createdMessage->message_body, $emailMessage->getBody());
        self::assertEquals($createdMessage->message_type, MessageTypes::EMAIL);
        self::assertFalse($createdMessage->is_incoming);

        $messageRecipient = $createdMessage->recipients()->first();
        self::assertNotEmpty($messageRecipient);
        self::assertNotEmpty($emailMessage->getTo());
        self::assertEquals($emailMessage->getTo()[0]->getName(), $messageRecipient->name);
        self::assertEquals($emailMessage->getTo()[0]->getAddress(), $messageRecipient->address);

        $latestStatusValue = $createdMessage->latestStatus()->value('status');
        self::assertEquals($latestStatusValue, MessageStatuses::DRAFT);
    }

    /**
     * @throws \Throwable
     */
    public function testCreateEmailMessageForwardForDelivery()
    {
        Queue::fake();
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, true);

        $latestStatusValue = $createdMessage->latestStatus()->value('status');
        self::assertEquals($latestStatusValue, MessageStatuses::READY_FOR_DELIVERY);

        Queue::assertPushedOn('messages', DeliverMessage::class, function ($job) use ($createdMessage) {
            return $job->getMessage()->id === $createdMessage->id;
        });
    }

    /**
     * @throws \Throwable
     */
    public function testCreateEmailMessageWithAttachments()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        $documentId = factory(Document::class)->create()->id;
        $attachment = [$documentId];

        $emailMessage->setAttachments($attachment);

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);

        $createdDocument = $createdMessage->documents()->first();

        self::assertNotEmpty($createdDocument);
        self::assertEquals(
            $createdDocument->id,
            $documentId
        );
    }

    /**
     * @throws \Throwable
     */
    public function testGetMessage()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();
        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);
        $message = $this->service->getMessage($createdMessage->id);
        self::assertEquals($createdMessage->id, $message->id);
    }

    public function testGetNonExistingMessage()
    {
        self::expectException(ModelNotFoundException::class);
        $this->service->getMessage(0);
    }

    /**
     * @throws \Throwable
     */
    public function testDeleteMessage()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);
        $this->service->deleteMessage($createdMessage->id);

        self::expectException(ModelNotFoundException::class);
        $this->service->getMessage($createdMessage->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDeleteNonDraftMessages()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);
        $createdMessage->changeStatus(MessageStatuses::DELIVERY_IN_PROGRESS);

        self::expectException(NotAllowedException::class);
        $this->service->deleteMessage($createdMessage->id);
    }

    /**
     * @throws \Throwable
     */
    public function testGetMessageStatus()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);

        $messageStatus = $this->service->getMessageStatus($createdMessage->id);
        self::assertEquals(MessageStatuses::DRAFT, $messageStatus);
    }

    /**
     * @throws \Throwable
     */
    public function testForwardForDelivery()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);
        $this->service->send($createdMessage->id);
        $forwardedMessageStatus = $this->service->getMessageStatus($createdMessage->id);

        self::assertEquals(
            MessageStatuses::READY_FOR_DELIVERY,
            $forwardedMessageStatus
        );
    }

    /**
     * @throws \Throwable
     */
    public function testForwardForDeliveryForNonDraftMessages()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);
        $createdMessage->changeStatus(MessageStatuses::READY_FOR_DELIVERY);

        self::expectException(NotAllowedException::class);
        $this->service->send($createdMessage->id);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateMessageWithoutForwardForDelivery()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);

        $emailMessageUpdated = MessageDataMock::getEmailMessageInstance();
        $updatedMessage = $this->service->updateOutgoingMessage($createdMessage->id, $emailMessageUpdated, false);

        self::assertEquals($createdMessage->id, $updatedMessage->id);
        self::assertEquals($createdMessage->message_type, $updatedMessage->message_type);

        $createdMessageStatus = $this->service->getMessageStatus($createdMessage->id);
        $updatedMessageStatus = $this->service->getMessageStatus($updatedMessage->id);

        self::assertEquals($createdMessageStatus, $updatedMessageStatus);
        self::assertEquals(MessageStatuses::DRAFT, $updatedMessageStatus);

        self::assertEquals($emailMessageUpdated->getSenderId(), $updatedMessage->sender_user_id);
        self::assertEquals($emailMessageUpdated->getSubject(), $updatedMessage->subject);
        self::assertEquals($emailMessageUpdated->getBody(), $updatedMessage->message_body);
    }

    /**
     * @throws \Throwable
     */
    public function testUpdateMessageWithForwardForDelivery()
    {
        Queue::fake();

        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);

        $emailMessageUpdated = MessageDataMock::getEmailMessageInstance();
        $updatedMessage = $this->service->updateOutgoingMessage($createdMessage->id, $emailMessageUpdated, true);

        self::assertEquals($createdMessage->id, $updatedMessage->id);
        self::assertEquals($createdMessage->message_type, $updatedMessage->message_type);

        Queue::assertPushedOn('messages', DeliverMessage::class, function ($job) use ($updatedMessage) {
            return $job->getMessage()->id === $updatedMessage->id;
        });
    }

    /**
     * @throws \Throwable
     */
    public function testFailToUpdateNonDraftMessage()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, true);

        $emailMessageUpdated = MessageDataMock::getEmailMessageInstance();
        self::expectException(NotAllowedException::class);
        $this->service->updateOutgoingMessage($createdMessage->id, $emailMessageUpdated, true);
    }

    /**
     * @throws \Throwable
     */
    public function testFailDeleteAttachmentWhileUpdating()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();
        $documentId = factory(Document::class)->create()->id;
        $attachment = [$documentId];
        $emailMessage->setAttachments($attachment);

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);

        $emailMessageUpdated = MessageDataMock::getEmailMessageInstance();

        $updatedMessage = $this->service->updateOutgoingMessage($createdMessage->id, $emailMessageUpdated, false);

        $updatedDocument = $updatedMessage->documents()->first();
        self::assertEmpty(
            $updatedDocument,
            'Attachment was not deleted in updated message but it has to be deleted.'
        );
    }

    /**
     * @throws \Throwable
     */
    public function testAddAttachmentWhileUpdating()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, false);

        $emailMessageUpdated = MessageDataMock::getEmailMessageInstance();
        $documentId = factory(Document::class)->create()->id;
        $attachment = [$documentId];
        $emailMessageUpdated->setAttachments($attachment);

        $updatedMessage = $this->service->updateOutgoingMessage($createdMessage->id, $emailMessageUpdated, false);

        $updatedDocument = $updatedMessage->documents()->first();

        self::assertNotEmpty($updatedDocument);
        self::assertEquals(
            $updatedDocument->id,
            $documentId
        );
    }

    /**
     * @doesNotPerformAssertions
     *
     * @throws \Throwable
     */
    public function testAttachDocument()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage);

        /** @var Document $document */
        $document = factory(Document::class)->create();

        $this->service->attachDocumentToMessage($createdMessage->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachDocumentIfAlreadyAttached()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var Document $document */
        $document = factory(Document::class)->create();
        $emailMessage->setAttachments([$document->id]);

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, true);

        self::expectException(NotAllowedException::class);
        $this->service->attachDocumentToMessage($createdMessage->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachDocumentToNonDraftMessage()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, true);

        /** @var Document $document */
        $document = factory(Document::class)->create();

        self::expectException(NotAllowedException::class);
        $this->service->attachDocumentToMessage($createdMessage->id, $document->id);
    }

    /**
     * @doesNotPerformAssertions
     *
     * @throws \Throwable
     */
    public function testDetachDocument()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var Document $document */
        $document = factory(Document::class)->create();
        $emailMessage->setAttachments([$document->id]);

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage);

        $this->service->detachDocumentFromMessage($createdMessage->id, $document->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDetachDocumentFromNonDraftMessage()
    {
        $emailMessage = MessageDataMock::getEmailMessageInstance();

        /** @var Document $document */
        $document = factory(Document::class)->create();
        $emailMessage->setAttachments([$document->id]);

        /** @var \App\Components\Messages\Models\Message $message */
        $createdMessage = $this->service->createOutgoingMessage($emailMessage, true);

        self::expectException(NotAllowedException::class);
        $this->service->attachDocumentToMessage($createdMessage->id, $document->id);
    }
}
