<?php

namespace App\Components\Search\Observers;

use App\Components\Locations\Events\UsersAttachedEvent;
use App\Components\Locations\Events\UsersDetachedEvent;
use App\Components\Locations\Models\Location;
use App\Components\Search\Models\UserAndTeam;

/**
 * Class LocationObserver
 *
 * @package App\Components\Search\Observers
 */
class LocationObserver
{
    /**
     * Handle the Location "deleting" event.
     *
     * @param  Location $location
     *
     * @return void
     */
    public function deleting(Location $location): void
    {
        $this->updateIndex($location);
    }

    /**
     * Handler for the UsersAttachedEvent event.
     *
     * @param UsersAttachedEvent $event
     */
    public static function usersAttached(UsersAttachedEvent $event): void
    {
        self::getUsersQuery($event->userIds)
            ->searchable();
    }

    /**
     * Handler for the UsersDetachedEvent event.
     *
     * @param  $event
     */
    public static function usersDetached(UsersDetachedEvent $event)
    {
        self::getUsersQuery($event->userIds)
            ->searchable();
    }

    /**
     * Updates index.
     *
     * @param  Location $location
     */
    private function updateIndex(Location $location): void
    {
        $userIds = $location->users->pluck('id')
            ->toArray();
        if (empty($userIds)) {
            return;
        }

        self::getUsersQuery($userIds)
            ->searchable();
    }

    /**
     * Returns users query.
     *
     * @param array $userIds Array of user ids.
     *
     * @return \App\Components\Search\Models\UserAndTeam
     */
    public static function getUsersQuery(array $userIds)
    {
        return UserAndTeam::where('type', UserAndTeam::TYPE_USER)
            ->whereIn('id', $userIds);
    }
}
