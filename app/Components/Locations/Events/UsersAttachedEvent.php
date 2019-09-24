<?php

namespace App\Components\Locations\Events;

use App\Components\Locations\Models\Location;
use Illuminate\Queue\SerializesModels;

/**
 * Class UsersAttachedEvent
 *
 * @package App\Components\Locations\Events
 */
class UsersAttachedEvent
{
    use SerializesModels;

    /**
     * Array of user ids that were attached to the location.
     *
     * @var array
     */
    public $userIds = [];

    /**
     * Location for which users were attached.
     *
     * @var Location
     */
    public $location;

    /**
     * UsersAttachedEvent constructor.
     *
     * @param Location $location Location for which users were attached.
     * @param array    $userIds  Array of users attached to the location.
     */
    public function __construct(Location $location, array $userIds)
    {
        $this->location = $location;
        $this->userIds  = $userIds;
    }
}
