<?php

namespace App\Components\Jobs;

use App\DefaultIndexConfigurator;

/**
 * Class JobTasksIndexConfigurator
 *
 * @package App\Components\Jobs
 */
class JobTasksIndexConfigurator extends DefaultIndexConfigurator
{
    /**
     * Name of the index.
     *
     * @var string
     */
    protected $name = 'job_tasks_index';
}
