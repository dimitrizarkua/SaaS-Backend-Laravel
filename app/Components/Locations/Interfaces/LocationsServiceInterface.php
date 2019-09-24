<?php

namespace App\Components\Locations\Interfaces;

use App\Components\Locations\Models\Location;

/**
 * Interface LocationsServiceInterface
 *
 * @package App\Components\Locations\Interfaces
 */
interface LocationsServiceInterface
{
    /**
     * Returns location by id.
     *
     * @param int $locationId Location id.
     *
     * @return Location
     */
    public function getLocation(int $locationId): Location;

    /**
     * Attach user to location.
     *
     * @param int  $locationId Id of location.
     * @param int  $userId     Id of user who attached to the location.
     * @param bool $primary    Defines if this location should be primary for user or not.
     */
    public function addUser(int $locationId, int $userId, bool $primary = false): void;

    /**
     * Detach user from location.
     *
     * @param int $locationId Id of location.
     * @param int $userId     Id of user who detached from the location.
     */
    public function removeUser(int $locationId, int $userId): void;

    /**
     * Attach suburb to location.
     *
     * @param int $locationId Id of location.
     * @param int $suburbId   Id of suburb which attached to the location.
     */
    public function addSuburb(int $locationId, int $suburbId): void;

    /**
     * Detach suburb from location.
     *
     * @param int $locationId Id of location.
     * @param int $suburbId   Id of suburb which detached from the location.
     */
    public function removeSuburb(int $locationId, int $suburbId): void;
}
