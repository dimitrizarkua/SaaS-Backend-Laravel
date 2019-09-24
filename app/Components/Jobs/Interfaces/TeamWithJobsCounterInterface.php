<?php

namespace App\Components\Jobs\Interfaces;

/**
 * Interface TeamWithJobsCounterInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface TeamWithJobsCounterInterface
{
    /**
     * Returns team id.
     *
     * @return int
     */
    public function getTeamId(): int;

    /**
     * Returns team name.
     *
     * @return string
     */
    public function getTeamName(): string;

    /**
     * Returns count of jobs for the team.
     *
     * @return int
     */
    public function getJobsCount(): int;
}
