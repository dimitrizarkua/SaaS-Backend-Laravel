<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Interfaces\JobsServiceInterface;

/**
 * Class JobsEntityService
 *
 * @package App\Components\Jobs\Services
 */
abstract class JobsEntityService
{
    /** @var JobsServiceInterface */
    private $jobsService = null;

    /**
     * @return \App\Components\Jobs\Interfaces\JobsServiceInterface
     */
    protected function jobsService(): JobsServiceInterface
    {
        if (!$this->jobsService) {
            $this->jobsService = app()->make(JobsServiceInterface::class);
        }

        return $this->jobsService;
    }
}
