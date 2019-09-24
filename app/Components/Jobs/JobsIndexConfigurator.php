<?php

namespace App\Components\Jobs;

use App\DefaultIndexConfigurator;

/**
 * Class JobsIndexConfigurator
 *
 * @package App\Components\Jobs
 */
class JobsIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'jobs_index';
}
