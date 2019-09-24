<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Http\Controllers\Controller;

/**
 * Class JobsControllerBase
 *
 * @package App\Http\Controllers\Jobs
 */
abstract class JobsControllerBase extends Controller
{
    /**
     * @var \App\Components\Jobs\Interfaces\JobsServiceInterface
     */
    protected $service;

    /**
     * JobsControllerBase constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobsServiceInterface $service
     */
    public function __construct(JobsServiceInterface $service)
    {
        $this->service = $service;
    }
}
