<?php

namespace App\Components\Notifications\Listeners;

use App\Components\Notifications\Events\UserMentioned;
use App\Jobs\Notifications\SendNotification;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class UserNotificationEventsListener
 *
 * @package Components\Notifications\Listeners
 */
class UserNotificationEventsListener
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $mapEventMethod = [
            UserMentioned::class => '@onUserMentioned',
        ];

        foreach ($mapEventMethod as $eventClassName => $method) {
            $events->listen($eventClassName, self::class . $method);
        }
    }

    /**
     * @param \App\Components\Notifications\Events\UserMentioned $event
     */
    public function onUserMentioned(UserMentioned $event): void
    {
        SendNotification::dispatch($event)
            ->onQueue('notifications');
    }
}
