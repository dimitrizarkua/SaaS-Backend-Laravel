<?php

namespace App\Console\Commands;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Console\WorkCommand;

/**
 * Class QueueConsumerCommand
 *
 * @package App\Console\Commands
 */
class QueueConsumerCommand extends WorkCommand
{
    protected $signature = 'queue:consumer
                            {connection? : The name of the queue connection to work}
                            {--queue= : The names of the queues to work}
                            {--daemon : Run the worker in daemon mode (Deprecated)}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--force : Force the worker to run even in maintenance mode}
                            {--memory=128 : The memory limit in megabytes}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=0 : Number of times to attempt a job before logging it failed}';

    /**
     * @inheritdoc
     */
    protected function writeStatus(Job $job, $status, $type)
    {
        $this->output->writeln(sprintf(
            "[%s] <{$type}>%s</{$type}> %s",
            $job->getJobId(),
            str_pad("{$status}:", 11),
            $job->resolveName()
        ));
    }
}
