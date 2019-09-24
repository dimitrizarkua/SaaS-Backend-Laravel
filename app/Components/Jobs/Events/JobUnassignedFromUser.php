<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use Illuminate\Queue\SerializesModels;

/**
 * Class JobUnassignedFromUser
 *
 * @package App\Components\Jobs\Events
 */
class JobUnassignedFromUser
{
    use SerializesModels;

    /** @var \App\Components\Jobs\Models\Job */
    public $job;

    public $userId;

    /**
     * Create a new event instance.
     *
     * @param Job $job
     * @param int $userId
     */
    public function __construct(Job $job, int $userId)
    {
        $this->job    = $job;
        $this->userId = $userId;
    }
}
