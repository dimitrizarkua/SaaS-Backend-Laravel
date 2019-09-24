<?php

namespace App\Components\Jobs\Listeners;

use App\Components\Jobs\Events\JobAssignedToTeam;
use App\Components\Jobs\Events\JobAssignedToUser;
use App\Components\Jobs\Events\JobCreated;
use App\Components\Jobs\Events\JobDeleted;
use App\Components\Jobs\Events\JobMessageReadOrUnread;
use App\Components\Jobs\Events\JobPinToggled;
use App\Components\Jobs\Events\JobStatusChanged;
use App\Components\Jobs\Events\JobUnassignedFromTeam;
use App\Components\Jobs\Events\JobUnassignedFromUser;
use App\Components\Jobs\Events\JobUpdated;
use App\Components\Jobs\Events\MessageAttachedToJob;
use App\Components\Jobs\Events\NoteAttachedToJob;
use App\Components\Jobs\Interfaces\JobListingServiceInterface;
use App\Components\Jobs\Models\JobTeam;
use App\Components\Jobs\Models\JobUser;
use App\Components\Messages\Events\MessageDelivered;
use App\Components\Teams\Models\TeamMember;
use App\Jobs\Notifications\SendNotification;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class JobEventsListener
 *
 * @package Components\Jobs\Listeners
 */
class JobEventsListener
{
    /** @var JobListingServiceInterface */
    private $jobListingService;

    /**
     * JobEventsListener constructor.
     *
     * @param JobListingServiceInterface $jobListingService
     */
    public function __construct(JobListingServiceInterface $jobListingService)
    {
        $this->jobListingService = $jobListingService;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events): void
    {
        $mapEventMethod = [
            JobCreated::class             => '@onJobCreated',
            JobUpdated::class             => '@onJobUpdated',
            JobDeleted::class             => '@onJobDeleted',
            JobStatusChanged::class       => '@onJobStatusChanged',
            JobPinToggled::class          => '@onPinToggled',
            JobAssignedToUser::class      => '@onAssignedToUser',
            MessageAttachedToJob::class   => '@onMessageAttachedToJob',
            NoteAttachedToJob::class      => '@onNoteAttachedToJob',
            JobUnassignedFromUser::class  => '@onUnassignedFromUser',
            JobAssignedToTeam::class      => '@onAssignedToTeam',
            JobUnassignedFromTeam::class  => '@onUnassignedFromTeam',
            MessageDelivered::class       => '@onMessageDelivered',
            JobMessageReadOrUnread::class => '@onJobMessageReadOrUnread',
        ];

        foreach ($mapEventMethod as $eventClassName => $method) {
            $events->listen($eventClassName, self::class . $method);
        }
    }

    /**
     * @param \App\Components\Jobs\Events\JobCreated $event
     */
    public function onJobCreated(JobCreated $event): void
    {
        $this->jobListingService->recalculateInboxCounter();
        if ($event->senderId) {
            $this->jobListingService->recalculateUsersCounters([$event->senderId]);
        }

        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Jobs\Events\JobUpdated $event
     */
    public function onJobUpdated(JobUpdated $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Jobs\Events\JobStatusChanged $event
     */
    public function onJobStatusChanged(JobStatusChanged $event): void
    {
        $assignedUserIds = JobUser::where('job_id', $event->job->id)
            ->pluck('user_id')
            ->toArray();

        $teamIds = JobTeam::where('job_id', $event->job->id)
            ->pluck('team_id')
            ->toArray();

        $teamsMembersIds = TeamMember::whereIn('team_id', $teamIds)
            ->pluck('user_id')
            ->toArray();

        $userIds = array_unique(array_merge($assignedUserIds, $teamsMembersIds));

        $this->jobListingService->recalculateAllCounters($userIds, $teamIds);
    }

    /**
     * @param \App\Components\Jobs\Events\JobDeleted $event
     */
    public function onJobDeleted(JobDeleted $event): void
    {
        $assignedUserIds = JobUser::where('job_id', $event->targetModel->id)
            ->pluck('user_id')
            ->toArray();

        $teamIds = JobTeam::where('job_id', $event->targetModel->id)
            ->pluck('team_id')
            ->toArray();

        $teamsMembersIds = TeamMember::whereIn('team_id', $teamIds)
            ->pluck('user_id')
            ->toArray();

        $userIds = array_unique(array_merge($assignedUserIds, $teamsMembersIds));

        $this->jobListingService->recalculateAllCounters($userIds, $teamIds);
    }

    public function onPinToggled(): void
    {
        // $this->jobListingService->recalculateCounters();
    }

    public function onJobMessageReadOrUnread(): void
    {
        // $this->jobListingService->recalculateUsersCounters();
    }

    /**
     * @param \App\Components\Jobs\Events\JobAssignedToUser $event
     *
     * @throws \Throwable
     */
    public function onAssignedToUser(JobAssignedToUser $event): void
    {
        $this->jobListingService->recalculateInboxCounter();
        $this->jobListingService->recalculateUsersCounters([$event->assignedUserId]);

        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Jobs\Events\MessageAttachedToJob $event
     */
    public function onMessageAttachedToJob(MessageAttachedToJob $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Messages\Events\MessageDelivered $event
     */
    public function onMessageDelivered(MessageDelivered $event): void
    {
        $job = $event->message->jobs()
            ->first();

        if (null !== $job) {
            SendNotification::dispatch(new JobUpdated($job, $event->message->sender_user_id))
                ->onQueue('notifications');
        }
    }

    /**
     * @param \App\Components\Jobs\Events\NoteAttachedToJob $event
     */
    public function onNoteAttachedToJob(NoteAttachedToJob $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }

    /**
     * @param \App\Components\Jobs\Events\JobUnassignedFromUser $event
     */
    public function onUnassignedFromUser(JobUnassignedFromUser $event): void
    {
        $this->jobListingService->recalculateInboxCounter();
        $this->jobListingService->recalculateUsersCounters([$event->userId]);
    }

    /**
     * @param JobAssignedToTeam $event
     */
    public function onAssignedToTeam(JobAssignedToTeam $event): void
    {
        $userIds = TeamMember::where('team_id', $event->teamId)
            ->pluck('user_id')
            ->toArray();

        $this->jobListingService->recalculateAllCounters($userIds, [$event->teamId]);
    }

    public function onUnassignedFromTeam(JobUnassignedFromTeam $event): void
    {
        $userIds = TeamMember::where('team_id', $event->teamId)
            ->pluck('user_id')
            ->toArray();

        $this->jobListingService->recalculateAllCounters($userIds, [$event->teamId]);
    }
}
