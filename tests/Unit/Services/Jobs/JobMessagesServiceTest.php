<?php

namespace Tests\Unit\Services\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Models\JobMessage;
use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Enums\MessageTypes;
use App\Components\Messages\Exceptions\NotAllowedException as MessageNotAllowedException;
use App\Components\Messages\Models\Message;
use App\Components\Messages\Models\MessageStatus;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Unit\Jobs\JobFaker;

/**
 * Class JobMessagesServiceTest
 *
 * @package Tests\Unit\Services\Jobs
 * @group   jobs
 * @group   services
 */
class JobMessagesServiceTest extends TestCase
{
    use DatabaseTransactions, JobFaker;

    /**
     * @var \App\Components\Jobs\Interfaces\JobMessagesServiceInterface
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->seed('ContactsSeeder');

        $this->service = Container::getInstance()->make(JobMessagesServiceInterface::class);
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->service);
    }

    /**
     * @throws \Throwable
     */
    public function testAttachMessage()
    {
        $job     = $this->fakeJobWithStatus();
        $message = factory(Message::class)->create();

        $this->service->attachMessage($job->id, $message->id);

        JobMessage::query()
            ->where([
                'message_id' => $message->id,
                'job_id'     => $job->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->messages()->count());
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachMessageToClosedJob()
    {
        $job     = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $message = factory(Message::class)->create();

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');
        $this->service->attachMessage($job->id, $message->id);
    }

    /**
     * @throws \Throwable
     */
    public function testAttachMessageSendImmediately()
    {
        $job  = $this->fakeJobWithStatus();
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $message = factory(Message::class)->create([
            'is_incoming'  => false,
            'message_type' => MessageTypes::EMAIL,
        ]);

        factory(MessageStatus::class)->create([
            'message_id' => $message->id,
            'status'     => MessageStatuses::DRAFT,
        ]);

        $this->service->attachMessage($job->id, $message->id, true);

        JobMessage::query()
            ->where([
                'message_id' => $message->id,
                'job_id'     => $job->id,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->messages()->count());

        Message::findOrFail($message->id);
        MessageStatus::query()
            ->where([
                'message_id' => $message->id,
                'status'     => MessageStatuses::READY_FOR_DELIVERY,
            ])
            ->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachMessageWasAlreadyAttached()
    {
        $job     = $this->fakeJobWithStatus();
        $message = factory(Message::class)->create();

        $this->service->attachMessage($job->id, $message->id);

        self::expectExceptionMessage('This message is already attached to specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->attachMessage($job->id, $message->id);
    }

    /**
     * @throws \Throwable
     */
    public function testDetachMessage()
    {
        $jobMessage = factory(JobMessage::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $this->service->detachMessage($jobMessage->job_id, $jobMessage->message_id);

        $jobMessage = JobMessage::query()
            ->where([
                'job_id'     => $jobMessage->job_id,
                'message_id' => $jobMessage->message_id,
            ])
            ->firstOrFail();

        self::assertNotNull($jobMessage->deleted_at);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDetachMessageFromClosedJob()
    {
        $job        = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );
        $jobMessage = factory(JobMessage::class)->create([
            'job_id' => $job->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');
        $this->service->detachMessage($jobMessage->job_id, $jobMessage->message_id);
    }

    /**
     * @throws \Throwable
     */
    public function testHasMessage()
    {
        $jobMessage = factory(JobMessage::class)->create([
            'job_id' => $this->fakeJobWithStatus()->id,
        ]);

        $hasMessage = $this->service->hasMessage($jobMessage->job_id, $jobMessage->message_id);

        self::assertTrue($hasMessage);
    }

    /**
     * @throws \Throwable
     */
    public function testHasNoMessage()
    {
        $job = $this->fakeJobWithStatus();

        $hasMessage = $this->service->hasMessage($job->id, 0);

        self::assertFalse($hasMessage);
    }

    /**
     * @throws \Throwable
     */
    public function testSendOutgoingMessage()
    {
        $job = $this->fakeJobWithStatus();

        $message = factory(Message::class)->create([
            'is_incoming'  => false,
            'message_type' => MessageTypes::EMAIL,
        ]);

        factory(MessageStatus::class)->create([
            'message_id' => $message->id,
            'status'     => MessageStatuses::DRAFT,
        ]);

        factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $this->service->sendMessage($job->id, $message->id);

        JobMessage::query()
            ->where([
                'job_id'     => $job->id,
                'message_id' => $message->id,
            ])
            ->firstOrFail();

        $messageStatus = MessageStatus::query()
            ->where([
                'message_id' => $message->id,
                'status'     => MessageStatuses::READY_FOR_DELIVERY,
            ])
            ->firstOrFail();

        self::assertEquals(1, $job->messages()->count());
        self::assertEquals($message->id, $messageStatus->message_id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToSendMessageForClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        $message = factory(Message::class)->create([
            'is_incoming'  => false,
            'message_type' => MessageTypes::EMAIL,
        ]);

        factory(MessageStatus::class)->create([
            'message_id' => $message->id,
            'status'     => MessageStatuses::DRAFT,
        ]);

        factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        self::expectExceptionMessage('Could not make changes to the closed or cancelled job.');
        $this->service->sendMessage($job->id, $message->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToSendIncomingMessage()
    {
        $job = $this->fakeJobWithStatus();

        $message = factory(Message::class)->create([
            'is_incoming'  => true,
            'message_type' => MessageTypes::EMAIL,
        ]);

        factory(MessageStatus::class)->create([
            'message_id' => $message->id,
            'status'     => MessageStatuses::DRAFT,
        ]);

        factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        self::expectException(MessageNotAllowedException::class);
        $this->service->sendMessage($job->id, $message->id);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToSendMessageThanNotAssignedToJob()
    {
        $job = $this->fakeJobWithStatus();

        $message = factory(Message::class)->create([
            'is_incoming'  => false,
            'message_type' => MessageTypes::EMAIL,
        ]);

        self::expectExceptionMessage('Message is not attached to specified job.');
        self::expectException(NotAllowedException::class);
        $this->service->sendMessage($job->id, $message->id);
    }

    /**
     * @throws \Throwable
     */
    public function testReadIncomingMessage()
    {
        $job             = $this->fakeJobWithStatus();
        $incomingMessage = factory(Message::class)->create([
            'is_incoming' => true,
        ]);

        factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $incomingMessage->id,
        ]);

        $this->service->readIncomingMessage($job->id, $incomingMessage->id);

        self::assertNotNull($job->incomingMessages()->first()->pivot->read_at);
    }

    /**
     * @throws \Throwable
     */
    public function testReadAllIncomingMessage()
    {
        $job              = $this->fakeJobWithStatus();
        $incomingMessages = factory(Message::class, $this->faker->numberBetween(1, 3))->create([
            'is_incoming' => true,
        ]);
        $job->incomingMessages()->attach($incomingMessages);

        $this->service->readAllIncomingMessages($job->id);

        $incomingMessages = $job->incomingMessages()->get();
        foreach ($incomingMessages as $msg) {
            self::assertNotNull($msg->pivot->read_at);
        }
    }

    public function testUnreadLatestIncomingMessage()
    {
        $job              = $this->fakeJobWithStatus();
        $incomingMessages = factory(Message::class, $this->faker->numberBetween(2, 3))->create([
            'is_incoming' => true,
        ]);
        /** @var Message $message */
        foreach ($incomingMessages as $message) {
            JobMessage::insert([
                'job_id'     => $job->id,
                'message_id' => $message->id,
                'created_at' => $this->faker->dateTime(), //set different values for correct ordering by this field
                'read_at'    => 'now()',
            ]);
        }

        $this->service->unreadLatestIncomingMessage($job->id);

        /** @var \App\Components\Jobs\Models\JobMessage $lastJobMessage */
        $lastJobMessage       = $job->incomingMessages()
            ->first()
            ->pivot;
        $readIncomingMessages = $job->incomingMessages()
            ->whereNotNull('read_at')
            ->get();

        self::assertNull($lastJobMessage->read_at);
        self::assertCount(count($incomingMessages) - 1, $readIncomingMessages);
    }

    public function testFailToUnreadLatestMessageWhenJobHasNoMessages()
    {
        $job = $this->fakeJobWithStatus();

        self::expectException(NotAllowedException::class);
        $this->service->unreadLatestIncomingMessage($job->id);
    }
}
