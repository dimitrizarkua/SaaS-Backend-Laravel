<?php

namespace App\Components\Locations\Events;

use App\Components\Locations\Models\Location;
use Illuminate\Queue\SerializesModels;

/**
 * Class UsersDetachedEvent
 *
 * @package App\Components\Locations\Events
 */
class UsersDetachedEvent
{
    use SerializesModels;

    /**
     * Array of users detached from the location.
     *
     * @var array
     */
    public $userIds = [];

    /**
     * Location from which users were detached.
     *
     * @var Location
     */
    public $location;

    /**
     * UsersAttachedEvent constructor.
     *
     * @param Location $location Location from which users were detached.
     * @param array    $userIds  Array of users detached from the location.
     */
    public function __construct(Location $location, array $userIds)
    {
        $this->location = $location;
        $this->userIds  = $userIds;
    }
}
