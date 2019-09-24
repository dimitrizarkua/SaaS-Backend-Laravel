<?php

namespace App\Components\Teams\Events;

use App\Components\Teams\Models\Team;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserAssignedToTeam
 *
 * @package App\Components\Teams\Events
 */
class UserAssignedToTeam
{
    use SerializesModels;

    /** @var \App\Components\Teams\Models\Team */
    public $team;

    public $userId;

    /**
     * Create a new event instance.
     *
     * @param \App\Components\Teams\Models\Team $team
     * @param int                               $userId
     */
    public function __construct(Team $team, int $userId)
    {
        $this->team   = $team;
        $this->userId = $userId;
    }
}
