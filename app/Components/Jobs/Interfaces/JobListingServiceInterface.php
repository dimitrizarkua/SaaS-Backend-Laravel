<?php

namespace App\Components\Jobs\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface JobListingServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobListingServiceInterface
{
    /**
     * Returns counters of jobs for specific user.
     *
     * @param int $userId User id.
     *
     * @return JobCountersInterface
     */
    public function getCountersAndTeams(int $userId): JobCountersInterface;

    /**
     * Returns jobs for the "Inbox" tab.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getInbox(): Collection;

    /**
     * Returns jobs which assigned or owner locations match to the user's.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getLocal(int $userId): Collection;

    /**
     * Returns jobs for the "Mine" tab.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMine(int $userId): Collection;

    /**
     * Returns list of jobs filtered by team.
     *
     * @param int $teamId Team id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getByTeam(int $teamId): Collection;

    /**
     * Returns jobs for the "Active" tab.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActive(int $userId): Collection;

    /**
     * Returns jobs for the "Closed" tab.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getClosed(int $userId): Collection;

    /**
     * Recalculates inbox job counters.
     */
    public function recalculateInboxCounter(): void;

    /**
     * Recalculates users job counters.
     *
     * @param array $userIds Array of user ids.
     */
    public function recalculateUsersCounters(array $userIds = []): void;

    /**
     * Recalculates teams job counters.
     *
     * @param array $teamIds Array of team ids.
     */
    public function recalculateTeamsCounters(array $teamIds = []): void;

    /**
     * Recalculates all job counters.
     *
     * @param array $userIds Array of user ids.
     * @param array $teamIds Array of team ids.
     */
    public function recalculateAllCounters(array $userIds = [], array $teamIds = []): void;

    /**
     * Returns list of jobs for folder "No contact 24 hours".
     *
     * @param int $userId User id.
     *
     * @return Collection
     */
    public function getNoContact24Hours(int $userId): Collection;

    /**
     * Returns list of jobs for folder "Upcoming KPI".
     *
     * @param int $userId User id.
     *
     * @return int
     */
    public function getUpcomingKpi(int $userId): Collection;
}
