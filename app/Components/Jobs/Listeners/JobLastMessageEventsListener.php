<?php

namespace App\Components\Jobs\Listeners;

use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Events\MessageAttachedToJob;
use App\Components\Messages\Events\MessageDetachedFromJob;
use App\Components\Messages\Models\Message;
use App\Components\Jobs\Events\NoteAttachedToJob;
use App\Components\Notes\Events\NoteDetachedFromJob;
use App\Components\Notes\Models\Note;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class JobLastMessageEventsListener
 *
 * @package App\Components\Jobs\Listeners
 */
class JobLastMessageEventsListener
{

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        //Messages
        $events->listen(
            MessageAttachedToJob::class,
            self::class . '@onMessageAttached'
        );

        $events->listen(
            MessageDetachedFromJob::class,
            self::class . '@onMessageDetached'
        );

        $events->listen(
            'eloquent.updated: ' . Message::class,
            self::class . '@onMessageUpdated'
        );

        $events->listen(
            'eloquent.deleted: ' . Message::class,
            self::class . '@onMessageDeleted'
        );

        //Notes
        $events->listen(
            NoteAttachedToJob::class,
            self::class . '@onNoteAttached'
        );

        $events->listen(
            NoteDetachedFromJob::class,
            self::class . '@onNoteDetached'
        );

        $events->listen(
            'eloquent.updated: ' . Note::class,
            self::class . '@onNoteUpdated'
        );

        $events->listen(
            'eloquent.deleted: ' . Note::class,
            self::class . '@onNoteDeleted'
        );
    }

    /**
     * @param \App\Components\Jobs\Events\MessageAttachedToJob $event
     */
    public function onMessageAttached(MessageAttachedToJob $event): void
    {
        $job = $event->targetModel;
        Job::find($job->id)->setLatestMessageByMessageAndNote();
    }

    /**
     * @param \App\Components\Messages\Events\MessageDetachedFromJob $event
     */
    public function onMessageDetached(MessageDetachedFromJob $event): void
    {
        Job::find($event->jobId)->setLatestMessageByMessageAndNote();
    }

    /**
     * @param \App\Components\Messages\Models\Message $event
     */
    public function onMessageUpdated(Message $event): void
    {
        foreach ($event->jobs as $job) {
            $job->setLatestMessageByMessageAndNote();
        }
    }

    /**
     * @param \App\Components\Messages\Models\Message $event
     */
    public function onMessageDeleted(Message $event)
    {
        foreach ($event->jobs as $job) {
            $job->setLatestMessageByMessageAndNote();
        }
    }

    /**
     * @param \App\Components\Jobs\Events\NoteAttachedToJob $event
     */
    public function onNoteAttached(NoteAttachedToJob $event): void
    {
        $job = $event->targetModel;
        Job::find($job->id)->setLatestMessageByMessageAndNote();
    }

    /**
     * @param \App\Components\Notes\Events\NoteDetachedFromJob $event
     */
    public function onNoteDetached(NoteDetachedFromJob $event): void
    {
        Job::find($event->jobId)->setLatestMessageByMessageAndNote();
    }

    /**
     * @param \App\Components\Notes\Models\Note $event
     */
    public function onNoteUpdated(Note $event)
    {
        foreach ($event->jobs as $job) {
            $job->setLatestMessageByMessageAndNote();
        }
    }

    /**
     * @param \App\Components\Notes\Models\Note $event
     */
    public function onNoteDeleted(Note $event)
    {
        foreach ($event->jobs as $job) {
            $job->setLatestMessageByMessageAndNote();
        }
    }
}
