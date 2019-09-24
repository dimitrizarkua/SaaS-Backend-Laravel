<?php

namespace Tests\Unit\LaravelJobs\Jobs;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Events\NoteAttachedToCreditNote;
use App\Components\Finance\Events\NoteAttachedToPurchaseOrder;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Events\JobAssignedToUser;
use App\Components\Jobs\Events\JobCreated;
use App\Components\Jobs\Events\JobUpdated;
use App\Components\Jobs\Events\MessageAttachedToJob;
use App\Components\Jobs\Events\NoteAttachedToJob;
use App\Components\Notifications\Events\UserMentioned;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobFollower;
use App\Components\Jobs\Models\JobUser;
use App\Components\Locations\Models\Location;
use App\Components\Messages\Models\Message;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Enums\NotificationSettingTypes;
use App\Components\Notifications\Events\UserNotificationCreated;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Components\Notifications\Models\UserNotificationSetting;
use App\Jobs\Notifications\SendNotification;
use App\Models\User;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Class SendNotificationTest
 *
 * @package Tests\Unit\LaravelJobs\Jobs
 *
 * @group   notifications
 * @group   jobs
 */
class SendNotificationTest extends TestCase
{
    /**
     * @var UserNotificationsServiceInterface
     */
    private $service;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->service = Container::getInstance()
            ->make(UserNotificationsServiceInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        unset($this->service);

        parent::tearDown();
    }

    /**
     * @throws \Throwable
     */
    public function testJobCreatedEvent()
    {
        factory(User::class)->create();
        /** @var Job $job */
        $job      = factory(Job::class)->create();
        $jobEvent = new JobCreated($job);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($job, $jobEvent) {
            $location     = Location::find($job->assigned_location_id);
            $resolvedBody = sprintf(JobCreated::TEXT, '#' . $job->id . '-' . $location->code);

            $body = json_decode($e->userNotification->body);

            return $e->userNotification->type === $jobEvent->getNotificationType()
                && $body->text === $resolvedBody
                && $body->target->id === $job->id
                && $body->target->type === 'job';
        });
    }

    /**
     * @throws \Throwable
     */
    public function testJobCreatedEventWithoutLocation()
    {
        factory(User::class)->create();
        /** @var Job $job */
        $job      = factory(Job::class)->create([
            'assigned_location_id' => null,
        ]);
        $jobEvent = new JobCreated($job);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($job, $jobEvent) {
            $resolvedBody = sprintf(JobCreated::TEXT, '#' . $job->id);
            $body         = json_decode($e->userNotification->body);

            return $e->userNotification->type === $jobEvent->getNotificationType()
                && $body->text === $resolvedBody;
        });
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testJobAssignedToSomeoneWhenSettingAssignedToSomeoneSwitchOff()
    {
        /** @var Job $job */
        $jobUser = factory(JobUser::class)->create();

        factory(UserNotificationSetting::class)->create([
            'user_id' => $jobUser->user_id,
            'type'    => NotificationSettingTypes::JOB_ASSIGNED_TO_SOMEONE,
            'value'   => false,
        ]);

        $someOne  = factory(User::class)->create();
        $job      = Job::find($jobUser->job_id);
        $jobEvent = new JobAssignedToUser($job, $someOne->id, $jobUser->user_id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatchedTimes(UserNotificationCreated::class, 1);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testJobAssignedToSomeoneWhenSettingAssignedToSomeoneSwitchOn()
    {
        /** @var Job $job */
        $jobUser = factory(JobUser::class)->create();
        $someOne = factory(User::class)->create();

        factory(UserNotificationSetting::class)->create([
            'user_id' => $jobUser->user_id,
            'type'    => NotificationSettingTypes::JOB_ASSIGNED_TO_SOMEONE,
            'value'   => true,
        ]);

        $job      = Job::find($jobUser->job_id);
        $jobEvent = new JobAssignedToUser($job, $someOne->id, $jobUser->user_id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatchedTimes(UserNotificationCreated::class, 1);
    }

    /**
     * @throws \JsonMapper_Exception
     */
    public function testJobAssignedToMeWhenSettingAssignedToMeSwitchOn()
    {
        /** @var Job $job */
        $someOne = factory(User::class)->create();
        $jobUser = factory(JobUser::class)->create();

        factory(UserNotificationSetting::class)->create([
            'user_id' => $jobUser->user_id,
            'type'    => NotificationSettingTypes::JOB_ASSIGNED_TO_ME,
            'value'   => true,
        ]);

        $job      = Job::find($jobUser->job_id);
        $jobEvent = new JobAssignedToUser($job, $jobUser->user_id, $someOne->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($job->assigned_location_id);
        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($job, $location, $jobEvent) {
            $resolvedBody = sprintf(
                JobAssignedToUser::TEXT,
                'You',
                'were',
                '#' . $job->id . '-' . $location->code
            );

            $body = json_decode($e->userNotification->body);

            return $e->userNotification->type === $jobEvent->getNotificationType()
                && $body->text === $resolvedBody;
        });
    }

    /**
     * @throws \Throwable
     */
    public function testJobAssignedToMeEvent()
    {
        // fake user
        $fakeUser = factory(User::class)->create();
        /** @var Job $job */
        $jobUser = factory(JobUser::class)->create();

        $job      = Job::find($jobUser->job_id);
        $jobEvent = new JobAssignedToUser($job, $jobUser->user_id, $fakeUser->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($job->assigned_location_id);
        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($job, $location, $jobEvent) {
            $resolvedBody = sprintf(
                JobAssignedToUser::TEXT,
                'You',
                'were',
                '#' . $job->id . '-' . $location->code
            );

            $body = json_decode($e->userNotification->body);

            return $e->userNotification->type === $jobEvent->getNotificationType()
                && $body->text === $resolvedBody;
        });
    }

    /**
     * @throws \Throwable
     */
    public function testJobAssignedToAnotherUserEvent()
    {
        factory(User::class)->create();
        $someOne  = factory(User::class)->create();
        $job      = factory(Job::class)->create();
        $jobEvent = new JobAssignedToUser($job, $someOne->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($job->assigned_location_id);
        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($job, $someOne, $location, $jobEvent) {
                $resolvedBody = sprintf(
                    JobAssignedToUser::TEXT,
                    $someOne->full_name,
                    'was',
                    '#' . $job->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $body->target->id === $job->id
                    && $body->target->type === 'job';
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function testUserMentionedInJobViaNoteEvent()
    {
        $author   = factory(User::class)->create();
        $user     = factory(User::class)->create();
        $job      = factory(Job::class)->create();
        $note     = factory(Note::class)->create();
        $jobEvent = new UserMentioned($job, $note, [$user->id], $author->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($job->assigned_location_id);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($job, $author, $note, $location, $jobEvent) {
                $resolvedBody = sprintf(
                    UserMentioned::TEXT,
                    $author->full_name,
                    'job',
                    $job->id,
                    '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $body->target->type === 'job'
                    && $body->target->id === $job->id
                    && $body->context->type === 'note'
                    && $body->context->id === $note->id;
            }
        );
    }

    /**
     * @throws \Throwable
     */
    public function testUserMentionedInContactViaNoteEvent()
    {
        $author       = factory(User::class)->create();
        $user         = factory(User::class)->create();
        $contact      = factory(Contact::class)->create();
        $note         = factory(Note::class)->create();
        $contactEvent = new UserMentioned($contact, $note, [$user->id], $author->id);

        Event::fake();
        $laravelJob = new SendNotification($contactEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($contact, $author, $contactEvent) {
                $resolvedBody = sprintf(UserMentioned::TEXT, $author->full_name, 'contact', $contact->id, '');

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $contactEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $body->target->type === 'contact'
                    && $body->target->id === $contact->id
                    && $body->context->type === 'note';
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testReturnsNullBodyIfUserNotMentioned()
    {
        $author   = factory(User::class)->create();
        $job      = factory(Job::class)->create();
        $note     = factory(Note::class)->create();
        $jobEvent = new UserMentioned($job, $note, [-1], $author->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertNotDispatched(
            UserNotificationCreated::class,
            function () {
                return true;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testMessageAttachedToJobEvent()
    {
        factory(User::class)->create(); // another user will be created in factory(Message::class)->create();
        /** @var Message $message */
        $message       = factory(Message::class)->create();
        $messageAuthor = User::findOrFail($message->sender_user_id);
        $job           = factory(Job::class)->create();
        $location      = Location::find($job->assigned_location_id);

        Event::fake();
        $jobEvent = new MessageAttachedToJob($job, $message);

        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($messageAuthor, $job, $message, $location, $jobEvent) {
                $resolvedBody = sprintf(
                    MessageAttachedToJob::TEXT,
                    $messageAuthor->full_name,
                    '#' . $job->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $body->target->type === 'job'
                    && $body->target->id === $job->id
                    && $body->context->type === 'message'
                    && $body->context->id === $message->id;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testMessageAttachedToJobEventIfRecipientEqualsToSender()
    {
        $message = factory(Message::class)->create();
        $job     = factory(Job::class)->create();

        Event::fake();
        $jobEvent = new MessageAttachedToJob($job, $message);

        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertNotDispatched(UserNotificationCreated::class);
    }

    /**
     * @throws \Exception
     */
    public function testNoteAttachedToJobEvent()
    {
        factory(User::class)->create();
        $note     = factory(Note::class)->create();
        $job      = factory(Job::class)->create();
        $jobEvent = new NoteAttachedToJob($job, $note);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);
        $location = Location::find($job->assigned_location_id);
        /** @var Note $note */
        $author = User::findOrFail($note->user_id);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($job, $author, $location, $jobEvent) {
                $resolvedBody = sprintf(
                    NoteAttachedToJob::TEXT,
                    $author->full_name,
                    '#' . $job->id . '-' . $location->code
                );
                $body         = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testNoteAttachedToJobEventNotSendToIfRecipientEqualsToSender()
    {
        $note     = factory(Note::class)->create();
        $job      = factory(Job::class)->create();
        $jobEvent = new NoteAttachedToJob($job, $note);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertNotDispatched(UserNotificationCreated::class);
    }

    /**
     * @throws \Exception
     */
    public function testNoteAttachedToSomeOneJobEvent()
    {
        $someOne = factory(User::class)->create();
        factory(User::class)->create();
        $note     = factory(Note::class)->create([
            'user_id' => $someOne->id,
        ]);
        $job      = factory(Job::class)->create();
        $jobEvent = new NoteAttachedToJob($job, $note);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($job->assigned_location_id);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($job, $someOne, $location, $jobEvent) {
                $resolvedBody = sprintf(
                    NoteAttachedToJob::TEXT,
                    $someOne->full_name,
                    '#' . $job->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testNotificationSettingSetToTrue()
    {
        factory(UserNotificationSetting::class)->create([
            'value' => true,
            'type'  => NotificationSettingTypes::JOB_CREATED,
        ]);

        /** @var Job $job */
        $job      = factory(Job::class)->create();
        $jobEvent = new JobCreated($job);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($job, $jobEvent) {
            $location     = Location::find($job->assigned_location_id);
            $resolvedBody = sprintf(JobCreated::TEXT, '#' . $job->id . '-' . $location->code);
            $body         = json_decode($e->userNotification->body);

            return $e->userNotification->type === $jobEvent->getNotificationType()
                && $body->text === $resolvedBody;
        });
    }

    /**
     * @throws \Exception
     */
    public function testNotificationSettingSetToFalse()
    {
        factory(UserNotificationSetting::class)->create([
            'value' => false,
            'type'  => NotificationSettingTypes::JOB_CREATED,
        ]);

        /** @var Job $job */
        $job      = factory(Job::class)->create();
        $jobEvent = new JobCreated($job);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertNotDispatched(UserNotificationCreated::class);
    }

    /**
     * @throws \Exception
     */
    public function testMessageOrNoteAttachedToUnassignedJobAndSettingsSetToTrue()
    {
        $user = factory(User::class)->create();
        factory(User::class)->create();
        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => true,
            'type'    => $this->faker->randomElement([
                NotificationSettingTypes::NOTE_ADDED_TO_MY_JOB,
                NotificationSettingTypes::MESSAGE_ADDED_TO_MY_JOB,
            ]),
        ]);

        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => true,
            'type'    => NotificationSettingTypes::JOB_CREATED,
        ]);

        $message = factory(Message::class)->create([
            'sender_user_id' => $user->id,
        ]);

        $job      = factory(Job::class)->create();
        $jobEvent = new MessageAttachedToJob($job, $message);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(UserNotificationCreated::class);
    }

    /**
     * @throws \Exception
     */
    public function testMessageAttachedToUnassignedJobAndSettingsSetToFalse()
    {
        $user = factory(User::class)->create();
        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => false,
            'type'    => NotificationSettingTypes::MESSAGE_ADDED_TO_UNASSIGNED_JOB,
        ]);

        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => true,
            'type'    => NotificationSettingTypes::JOB_CREATED,
        ]);

        $message = factory(Message::class)->create([
            'sender_user_id' => $user->id,
        ]);

        $job = factory(Job::class)->create();

        $jobEvent = new MessageAttachedToJob($job, $message);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertNotDispatched(UserNotificationCreated::class);
    }

    /**
     * @throws \Exception
     */
    public function testMessageAttachedToAssignedJobToSomeOne()
    {
        $user    = factory(User::class)->create();
        $someOne = factory(User::class)->create();

        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => true,
            'type'    => NotificationSettingTypes::MESSAGE_ADDED_TO_JOB_OWNED_BY_SOMEONE,
        ]);

        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => true,
            'type'    => NotificationSettingTypes::JOB_CREATED,
        ]);

        $message = factory(Message::class)->create([
            'sender_user_id' => $user->id,
        ]);

        $job = factory(Job::class)->create();

        factory(JobUser::class)->create([
            'user_id' => $someOne->id,
            'job_id'  => $job->id,
        ]);

        $jobEvent = new MessageAttachedToJob($job, $message);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($someOne) {
            return $e->userNotification->type === MessageAttachedToJob::TYPE
                && $e->userNotification->user_id === $someOne->id;
        });
    }

    /**
     * @throws \Exception
     */
    public function testMessageAttachedToAssignedJobToSomeOneSetSettingToFalse()
    {
        $user    = factory(User::class)->create();
        $someOne = factory(User::class)->create();

        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => false,
            'type'    => NotificationSettingTypes::MESSAGE_ADDED_TO_JOB_OWNED_BY_SOMEONE,
        ]);

        factory(UserNotificationSetting::class)->create([
            'user_id' => $user->id,
            'value'   => true,
            'type'    => NotificationSettingTypes::JOB_CREATED,
        ]);

        $message = factory(Message::class)->create([
            'sender_user_id' => $user->id,
        ]);

        $job = factory(Job::class)->create();

        factory(JobUser::class)->create([
            'user_id' => $someOne->id,
            'job_id'  => $job->id,
        ]);

        $jobEvent = new MessageAttachedToJob($job, $message);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertNotDispatched(UserNotificationCreated::class, function ($e) use ($user) {
            return $e->userNotification->type === MessageAttachedToJob::TYPE
                && $e->userNotification->user_id === $user->id;
        });

        Event::assertDispatched(UserNotificationCreated::class, function ($e) use ($someOne) {
            return $e->userNotification->type === MessageAttachedToJob::TYPE
                && $e->userNotification->user_id === $someOne->id;
        });
    }

    /**
     * @throws \Exception
     */
    public function testMessageAttachedToUnassignedJobAndSettingsNotSet()
    {
        $user = factory(User::class)->create();
        factory(User::class)->create();

        $message = factory(Message::class)->create([
            'sender_user_id' => $user->id,
        ]);

        $job      = factory(Job::class)->create();
        $jobEvent = new MessageAttachedToJob($job, $message);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(UserNotificationCreated::class);
    }

    /**
     * @throws \Exception
     */
    public function testSomeUserJobUpdatedForFollowers()
    {
        $user        = factory(User::class)->create();
        $job         = factory(Job::class)->create();
        $jobFollower = factory(JobFollower::class)->create([
            'job_id' => $job->id,
        ]);
        $follower    = User::find($jobFollower->user_id);
        $jobEvent    = new JobUpdated($job, $user->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function (UserNotificationCreated $e) use ($job, $jobEvent, $follower, $user) {
                $location     = Location::find($job->assigned_location_id);
                $resolvedBody = sprintf(
                    JobUpdated::TEXT,
                    $user->full_name,
                    '#' . $job->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $e->userNotification->user_id === $follower->id;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testSomeUserJobUpdatedForFollowersWithoutFullName()
    {
        $user = factory(User::class)->create([
            'first_name' => null,
            'last_name'  => null,
        ]);

        $job         = factory(Job::class)->create();
        $jobFollower = factory(JobFollower::class)->create([
            'job_id' => $job->id,
        ]);
        $follower    = User::find($jobFollower->user_id);
        $jobEvent    = new JobUpdated($job, $user->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function (UserNotificationCreated $e) use ($job, $jobEvent, $follower, $user) {
                $location     = Location::find($job->assigned_location_id);
                $resolvedBody = sprintf(
                    JobUpdated::TEXT,
                    $user->email,
                    '#' . $job->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $e->userNotification->user_id === $follower->id;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testJobUpdatedForFollowers()
    {
        $user        = factory(User::class)->create();
        $job         = factory(Job::class)->create();
        $jobFollower = factory(JobFollower::class)->create([
            'job_id' => $job->id,
        ]);
        $follower    = User::find($jobFollower->user_id);

        $jobEvent = new JobUpdated($job, $user->id);

        Event::fake();
        $laravelJob = new SendNotification($jobEvent);

        $laravelJob->handle($this->service);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function (UserNotificationCreated $e) use ($user, $job, $jobEvent, $follower) {
                $location     = Location::find($job->assigned_location_id);
                $resolvedBody = sprintf(
                    JobUpdated::TEXT,
                    $user->full_name,
                    '#' . $job->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $jobEvent->getNotificationType()
                    && $body->text === $resolvedBody
                    && $e->userNotification->user_id === $follower->id;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testNoteAttachedToPurchaseOrderEvent()
    {
        factory(User::class)->create();
        $note   = factory(Note::class)->create();
        $author = User::find($note->user_id);
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder      = factory(PurchaseOrder::class)->create();
        $purchaseOrderEvent = new NoteAttachedToPurchaseOrder($purchaseOrder, $note);

        Event::fake();
        $laravelJob = new SendNotification($purchaseOrderEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($purchaseOrder->location_id);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($purchaseOrder, $author, $location, $purchaseOrderEvent) {
                $resolvedBody = sprintf(
                    NoteAttachedToPurchaseOrder::TEXT,
                    $author->full_name,
                    '#' . $purchaseOrder->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $purchaseOrderEvent->getNotificationType()
                    && $body->text === $resolvedBody;
            }
        );
    }

    /**
     * @throws \Exception
     */
    public function testNoteAttachedToCreditNoteEvent()
    {
        factory(User::class)->create();
        $note   = factory(Note::class)->create();
        $author = User::find($note->user_id);
        /** @var CreditNote $creditNote */
        $creditNote      = factory(CreditNote::class)->create();
        $creditNoteEvent = new NoteAttachedToCreditNote($creditNote, $note);

        Event::fake();
        $laravelJob = new SendNotification($creditNoteEvent);

        $laravelJob->handle($this->service);

        $location = Location::find($creditNote->location_id);

        Event::assertDispatched(
            UserNotificationCreated::class,
            function ($e) use ($creditNote, $author, $location, $creditNoteEvent) {
                $resolvedBody = sprintf(
                    NoteAttachedToCreditNote::TEXT,
                    $author->full_name,
                    '#' . $creditNote->id . '-' . $location->code
                );

                $body = json_decode($e->userNotification->body);

                return $e->userNotification->type === $creditNoteEvent->getNotificationType()
                    && $body->text === $resolvedBody;
            }
        );
    }
}
