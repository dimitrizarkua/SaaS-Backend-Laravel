<?php

namespace Tests\Integration\Notifications;

use App\Components\Contacts\Interfaces\ContactsServiceInterface;
use App\Components\Contacts\Models\Contact;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Events\JobUpdated;
use App\Components\Notifications\Events\UserMentioned;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Messages\Events\MessageDelivered;
use App\Components\Messages\Interfaces\MessageDeliveryServiceInterface;
use App\Components\Messages\Interfaces\MessagingServiceInterface;
use App\Components\Messages\Models\MessageParticipantData;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\NoteData;
use App\Jobs\Messages\DeliverMessage;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;
use Tests\Unit\Messaging\MessageDataMock;

/**
 * Class UserNotificationsTest
 *
 * @package Tests\Integration\Notifications
 */
class UserNotificationsTest extends TestCase
{
    use JobFaker;
    /**
     * @var \App\Components\Messages\Interfaces\MessagingServiceInterface
     */
    private $messagingService;

    /**
     * @var \App\Components\Notes\Interfaces\NotesServiceInterface
     */
    private $notesService;

    /**
     * @var \App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
     */
    private $jobMessageService;

    /**
     * @var \App\Components\Jobs\Interfaces\JobNotesServiceInterface;
     */
    private $jobNotesService;

    /**
     * @var \App\Components\Messages\Interfaces\MessageDeliveryServiceInterface
     */
    private $messageDeliveryService;

    /**
     * @var \App\Components\Contacts\Interfaces\ContactsServiceInterface
     */
    private $contactService;

    public function setUp()
    {
        parent::setUp();

        $this->messagingService       = Container::getInstance()->make(MessagingServiceInterface::class);
        $this->notesService           = Container::getInstance()->make(NotesServiceInterface::class);
        $this->jobMessageService      = Container::getInstance()->make(JobMessagesServiceInterface::class);
        $this->jobNotesService        = Container::getInstance()->make(JobNotesServiceInterface::class);
        $this->contactService         = Container::getInstance()->make(ContactsServiceInterface::class);
        $this->messageDeliveryService = Container::getInstance()->make(MessageDeliveryServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset(
            $this->messagingService,
            $this->notesService,
            $this->jobMessageService,
            $this->messageDeliveryService,
            $this->contactService
        );
    }

    /**
     * @throws \Throwable
     */
    public function testCreateOutgoingMessageWithMentionedUsers()
    {
        $user = factory(User::class)->create();
        $job  = $this->fakeJobWithStatus(JobStatuses::NEW);

        $emailInstance = MessageDataMock::getEmailMessageInstance();
        $emailInstance->setBody(sprintf('[USER_ID:%d]', $user->id));

        Event::fake();

        $messageAdded = $this->messagingService->createOutgoingMessage($emailInstance);
        $this->jobMessageService->attachMessage($job->id, $messageAdded->id);

        Event::assertDispatched(
            UserMentioned::class,
            function ($e) use ($messageAdded, $user) {
                return $e->senderId === $messageAdded->sender_user_id
                    && in_array($user->id, $e->mentionedUserIds);
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function testStoreIncomingMessageWithMentionedUsers()
    {
        $user = factory(User::class)->create();
        $job  = $this->fakeJobWithStatus(JobStatuses::NEW);

        $emailInstance = MessageDataMock::getEmailMessageInstance();
        $emailInstance->setBody(sprintf('[USER_ID:%d]', $user->id));
        $emailInstance->setFrom(new MessageParticipantData($this->faker->email, $this->faker->name));

        Event::fake();

        $messageAdded = $this->messagingService->storeIncomingMessage($emailInstance);
        $this->jobMessageService->attachMessage($job->id, $messageAdded->id);

        Event::assertDispatched(
            UserMentioned::class,
            function ($e) use ($messageAdded, $user) {
                return $e->senderId === $messageAdded->sender_user_id
                    && in_array($user->id, $e->mentionedUserIds);
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function testSendMessageJobUpdatedEvent()
    {
        Event::fake();

        $user = factory(User::class)->create();
        $this->actingAs($user);
        $job = $this->fakeJobWithStatus(JobStatuses::NEW);

        $emailInstance = MessageDataMock::getEmailMessageInstance();
        $emailInstance->setBody(sprintf('[USER_ID:%d]', $user->id));
        $emailInstance->setFrom(new MessageParticipantData($this->faker->email, $this->faker->name));

        $messageAdded = $this->messagingService->createOutgoingMessage($emailInstance);
        $this->jobMessageService->attachMessage($job->id, $messageAdded->id);
        $this->jobMessageService->sendMessage($job->id, $messageAdded->id);

        $laravelJob = new DeliverMessage($messageAdded);
        $laravelJob->handle($this->messageDeliveryService);

        Event::assertDispatched(MessageDelivered::class, function (MessageDelivered $e) use ($messageAdded, $user) {
            return $e->message->id === $messageAdded->id;
        });
    }

    /**
     * @throws \Throwable
     */
    public function testAddNoteToJobWithMentionedUsers()
    {
        Event::fake();

        $user     = factory(User::class)->create();
        $job      = $this->fakeJobWithStatus(JobStatuses::NEW);
        $noteData = new NoteData(sprintf('[USER_ID:%d]', $user->id), $user->id);

        $noteAdded = $this->notesService->addNote($noteData);

        $this->jobNotesService->addNote($job->id, $noteAdded->id);

        Event::assertDispatched(
            UserMentioned::class,
            function (UserMentioned $e) use ($user, $job, $noteAdded) {
                return $e->senderId === $user->id
                    && in_array($user->id, $e->mentionedUserIds)
                    && $e->targetModel->id === $job->id
                    && $e->contextModel->id === $noteAdded->id;
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function testAddNoteToContactWithMentionedUsers()
    {
        Event::fake();

        $user     = factory(User::class)->create();
        $contact  = factory(Contact::class)->create();
        $noteData = new NoteData(sprintf('[USER_ID:%d]', $user->id), $user->id);

        $noteAdded = $this->notesService->addNote($noteData);

        $this->contactService->addNote($contact->id, $noteAdded->id);

        Event::assertDispatched(
            UserMentioned::class,
            function (UserMentioned $e) use ($user, $contact, $noteAdded) {
                return $e->senderId === $user->id
                    && in_array($user->id, $e->mentionedUserIds)
                    && $e->targetModel->id === $contact->id
                    && $e->contextModel->id === $noteAdded->id;
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function testAddNoteJobUpdatedEvent()
    {
        Event::fake();

        $user = factory(User::class)->create();
        $this->actingAs($user);
        $job      = $this->fakeJobWithStatus(JobStatuses::NEW);
        $noteData = new NoteData(sprintf('[USER_ID:%d]', $user->id), $user->id);

        $noteAdded = $this->notesService->addNote($noteData);

        $this->jobNotesService->addNote($job->id, $noteAdded->id);

        Event::assertDispatched(
            JobUpdated::class,
            function (JobUpdated $e) use ($user, $job, $noteAdded) {
                return $e->senderId === $user->id
                    && $e->targetModel->id === $job->id;
            }
        );
    }
}
