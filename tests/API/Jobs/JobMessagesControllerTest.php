<?php

namespace Tests\API\Jobs;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobMessage;
use App\Components\Jobs\Models\JobNotesTemplate;
use App\Components\Messages\Enums\MessageStatuses;
use App\Components\Messages\Models\Message;
use App\Models\User;
use Carbon\Carbon;

/**
 * Class JobMessagesControllerTest
 *
 * @package Tests\API\Jobs
 * @group   jobs
 * @group   api
 */
class JobMessagesControllerTest extends JobTestCase
{
    protected $permissions = [
        'jobs.view',
        'jobs.manage_messages',
        'jobs.manage_messages_detach',
    ];

    public function testListMessages()
    {
        $job   = $this->fakeJobWithStatus();
        $count = $this->faker->numberBetween(1, 5);
        factory(JobMessage::class, $count)->create(['job_id' => $job->id]);

        $url = action('Jobs\JobMessagesController@listMessages', ['job_id' => $job->id,]);

        $this->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount($count, 'data');
    }

    /**
     * @throws \Throwable
     */
    public function testAttachMessageToJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);
        $this->postJson($url)->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        JobMessage::query()->where([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ])->firstOrFail();
    }

    /**
     * @throws \Throwable
     */
    public function testFailAttachMessageToClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);
        $this->postJson($url)->assertStatus(405);
    }

    /**
     * @throws \Throwable
     */
    public function testAttachMessageToJobAndSendImmediately()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);
        $this->postJson($url, ['send_immediately' => true,])
            ->assertStatus(200);

        $reloaded = Job::find($job->id);
        self::assertTrue($job->touched_at->lt($reloaded->touched_at));

        JobMessage::query()->where([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ])->firstOrFail();

        self::assertEquals(MessageStatuses::READY_FOR_DELIVERY, $message->getCurrentStatus());
    }

    public function testFailToAttachMessageOwnedByOtherUser()
    {
        $job = $this->fakeJobWithStatus();

        /** @var User $user */
        $user = factory(User::class)->create();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $user->id]);

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $this->postJson($url)
            ->assertStatus(403)
            ->assertSee('You are not authorized to perform this action.');
    }

    /**
     * @throws \Throwable
     */
    public function testFailToAttachWhenAlreadyAdded()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $this->postJson($url)
            ->assertStatus(405)
            ->assertSee('This message is already attached to specified job.');
    }

    /**
     * @throws \Throwable
     */
    public function testAttachMessageWhenMessageIsNonDraft()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::READY_FOR_DELIVERY);

        self::assertFalse($message->isDraft());

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $this->postJson($url)
            ->assertStatus(405)
            ->assertSee('Only draft outgoing messages can be attached to a job.');
    }

    /**
     * @throws \Throwable
     */
    public function testAttachMessageWhenMessageIsIncoming()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create([
            'sender_user_id' => $this->user->id,
            'is_incoming'    => true,
        ]);
        $message->changeStatus(MessageStatuses::DRAFT);

        $url = action('Jobs\JobMessagesController@attachMessage', [
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $this->postJson($url)
            ->assertStatus(405)
            ->assertSee('Only draft outgoing messages can be attached to a job.');
    }

    /**
     * @throws \Throwable
     */
    public function testSendJobMessage()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        /** @var JobMessage $jobMessage */
        $jobMessage = factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@sendMessage', [
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ]);

        $this->postJson($url)
            ->assertStatus(200);

        self::assertEquals(MessageStatuses::READY_FOR_DELIVERY, $message->getCurrentStatus());
    }

    /**
     * @throws \Throwable
     */
    public function testFailSendClosedJobMessage()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        /** @var JobMessage $jobMessage */
        $jobMessage = factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@sendMessage', [
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ]);

        $this->postJson($url)->assertStatus(405);
    }

    /**
     * @throws \Throwable
     */
    public function testDetachMessageFromJob()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        /** @var JobMessage $jobMessage */
        $jobMessage = factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@detachMessage', [
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ]);

        $this->deleteJson($url)->assertStatus(200);

        /** @var JobMessage $jobMessage */
        $jobMessage = JobMessage::query()->where([
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ])->firstOrFail();

        self::assertNotNull($jobMessage->deleted_at);
    }

    /**
     * @throws \Throwable
     */
    public function testFailDetachMessageFromClosedJob()
    {
        $job = $this->fakeJobWithStatus(
            $this->faker->randomElement(JobStatuses::$closedStatuses)
        );

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::DRAFT);

        /** @var JobMessage $jobMessage */
        $jobMessage = factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@detachMessage', [
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ]);

        $this->deleteJson($url)->assertStatus(405);
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDetachMessageFromJobIfMessageIsNonDraft()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create(['sender_user_id' => $this->user->id]);
        $message->changeStatus(MessageStatuses::READY_FOR_DELIVERY);

        self::assertFalse($message->isDraft());

        /** @var JobMessage $jobMessage */
        $jobMessage = factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@detachMessage', [
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(405)
            ->assertSee('Sent and incoming messages can\'t be detached.');
    }

    /**
     * @throws \Throwable
     */
    public function testFailToDetachMessageFromJobIfMessageIsIncoming()
    {
        $job = $this->fakeJobWithStatus();

        /** @var Message $message */
        $message = factory(Message::class)->create([
            'sender_user_id' => $this->user->id,
            'is_incoming'    => true,
        ]);
        $message->changeStatus(MessageStatuses::DRAFT);

        /** @var JobMessage $jobMessage */
        $jobMessage = factory(JobMessage::class)->create([
            'job_id'     => $job->id,
            'message_id' => $message->id,
        ]);

        $url = action('Jobs\JobMessagesController@detachMessage', [
            'job_id'     => $jobMessage->job_id,
            'message_id' => $jobMessage->message_id,
        ]);

        $this->deleteJson($url)
            ->assertStatus(405)
            ->assertSee('Sent and incoming messages can\'t be detached.');
    }

    public function testComposeMessageSuccess()
    {
        $job      = $this->fakeJobWithStatus();
        $template = factory(JobNotesTemplate::class)->create([
            'body' => '{{$id}}',
        ]);

        $url = action('Jobs\JobMessagesController@composeMessage', [
            'job_id' => $job->id,
        ]);

        $response = $this->postJson($url, ['template_id' => $template->id,])
            ->assertStatus(200)
            ->assertSeeData();

        self::assertEquals($response->getData(), (string)$job->id);
    }

    public function testMarkMessagesAsRead()
    {
        $job              = $this->fakeJobWithStatus();
        $incomingMessages = factory(Message::class, $this->faker->numberBetween(1, 3))->create([
            'is_incoming' => true,
        ]);
        $job->incomingMessages()->attach($incomingMessages);

        $url = action('Jobs\JobMessagesController@markMessagesAsRead', [
            'job_id' => $job->id,
        ]);

        $this->patchJson($url)
            ->assertStatus(200);

        $readMessagesCount = JobMessage::query()->where('job_id', $job->id)
            ->whereNotNull('read_at')
            ->count();

        self::assertEquals($readMessagesCount, count($incomingMessages));
    }

    public function testMarkLatestMessageAsUnread()
    {
        $job              = $this->fakeJobWithStatus();
        $incomingMessages = factory(Message::class, $this->faker->numberBetween(2, 3))->create([
            'is_incoming' => true,
        ]);
        $job->incomingMessages()->attach($incomingMessages, [
            'read_at' => Carbon::now(),
        ]);

        $url = action('Jobs\JobMessagesController@markLatestMessageAsUnread', [
            'job_id' => $job->id,
        ]);

        $this->patchJson($url)
            ->assertStatus(200);

        $unreadMessagesCount = JobMessage::query()->where('job_id', $job->id)
            ->whereNull('read_at')
            ->count();

        self::assertEquals($unreadMessagesCount, 1);
    }
}
