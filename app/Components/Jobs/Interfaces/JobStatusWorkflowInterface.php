<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\Job;

/**
 * Interface JobStatusWorkflowInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobStatusWorkflowInterface extends StatusWorkflowInterface
{
    /**
     * Set current job.
     *
     * @param \App\Components\Jobs\Models\Job $job
     *
     * @return \App\Components\Jobs\Interfaces\JobStatusWorkflowInterface
     */
    public function setJob(Job $job): self;
}
