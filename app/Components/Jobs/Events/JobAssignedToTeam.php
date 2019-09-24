<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use Illuminate\Queue\SerializesModels;

/**
 * Class JobAssignedToTeam
 *
 * @package App\Components\Jobs\Events
 */
class JobAssignedToTeam
{
    use SerializesModels;

    /** @var \App\Components\Jobs\Models\Job */
    public $job;

    public $teamId;

    /**
     * Create a new event instance.
     *
     * @param Job $job
     * @param int $teamId
     */
    public function __construct(Job $job, int $teamId)
    {
        $this->job    = $job;
        $this->teamId = $teamId;
    }
}
