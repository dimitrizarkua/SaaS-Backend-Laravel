<?php

namespace App\Components\Jobs\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface JobCountersInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobCountersInterface
{
    /**
     * Returns jobs count for "Inbox" tab.
     *
     * @return int
     */
    public function getInboxCount(): int;

    /**
     * Returns jobs count for "Mine" tab.
     *
     * @return int
     */
    public function getMineCount(): int;

    /**
     * Returns jobs counters for "Teams" folder (aka user teams).
     *
     * @return Collection|TeamWithJobsCounterInterface[]
     */
    public function getTeams(): Collection;

    /**
     * Returns jobs count for "All active Jobs" tab.
     *
     * @return int
     */
    public function getAllActiveJobsCount(): int;

    /**
     * Returns jobs count for "Closed" tab.
     *
     * @return int
     */
    public function getClosedCount(): int;

    /**
     * Returns count of jobs for folder "No contact 24 hours".
     *
     * @return int
     */
    public function getNoContact24HoursCount(): int;

    /**
     * Returns count of jobs for folder "Upcoming KPI".
     *
     * @return int
     */
    public function getUpcomingKPICount(): int;
}
