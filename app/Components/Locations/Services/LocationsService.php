<?php

namespace App\Components\Locations\Services;

use App\Components\Locations\Events\UsersAttachedEvent;
use App\Components\Locations\Events\UsersDetachedEvent;
use App\Components\Locations\Exceptions\NotAllowedException;
use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Locations\Models\Location;

/**
 * Class LocationsService
 *
 * @package App\Components\Locations\Services
 */
class LocationsService implements LocationsServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getLocation(int $locationId): Location
    {
        return Location::findOrFail($locationId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function addUser(int $locationId, int $userId, bool $primary = false): void
    {
        $location = $this->getLocation($locationId);
        try {
            $location->users()->attach($userId, ['primary' => $primary]);
        } catch (\Exception $exception) {
            // If user has already attached to location then just update primary attribute.
            $location->users()->updateExistingPivot($userId, [
                'primary' => $primary,
            ]);
        }

        event(new UsersAttachedEvent($location, [$userId]));
    }

    /**
     * {@inheritdoc}
     */
    public function removeUser(int $locationId, int $userId): void
    {
        $location = $this->getLocation($locationId);
        $location->users()->detach($userId);
        event(new UsersDetachedEvent($location, [$userId]));
    }

    /**
     * {@inheritdoc}
     */
    public function addSuburb(int $locationId, int $suburbId): void
    {
        $location = $this->getLocation($locationId);
        try {
            $location->suburbs()->attach($suburbId);
        } catch (\Exception $exception) {
            throw new NotAllowedException('This suburb has been already added earlier to this location.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeSuburb(int $locationId, int $suburbId): void
    {
        $location = $this->getLocation($locationId);
        $location->suburbs()->detach($suburbId);
    }
}
