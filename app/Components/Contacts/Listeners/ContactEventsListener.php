<?php

namespace App\Components\Contacts\Listeners;

use App\Components\Contacts\Events\NoteAttachedToContact;
use App\Jobs\Notifications\SendNotification;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class ContactEventsListener
 *
 * @package Components\Jobs\Listeners
 */
class ContactEventsListener
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $mapEventMethod = [
            NoteAttachedToContact::class => '@onNoteAttachedToContact',
        ];

        foreach ($mapEventMethod as $eventClassName => $method) {
            $events->listen($eventClassName, self::class . $method);
        }
    }

    /**
     * @param \App\Components\Contacts\Events\NoteAttachedToContact $event
     */
    public function onNoteAttachedToContact(NoteAttachedToContact $event)
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }
}
