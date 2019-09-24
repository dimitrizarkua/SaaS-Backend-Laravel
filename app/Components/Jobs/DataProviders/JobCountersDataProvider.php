<?php

namespace App\Components\Jobs\DataProviders;

use App\Components\Jobs\Enums\JobCountersCacheKeys;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use Illuminate\Support\Carbon;

/**
 * Class JobCountersDataProvider
 *
 * @package App\Components\Jobs\DataProviders
 */
class JobCountersDataProvider
{
    /**
     * @var \Illuminate\Cache\TaggedCache
     */
    private $cache;
    /**
     * @var JobListingsDataProvider
     */
    private $listingDataProvider;

    /**
     * JobCountersDataProvider constructor.
     *
     * @param JobListingsDataProvider $listingDataProvider
     */
    public function __construct(JobListingsDataProvider $listingDataProvider)
    {
        $this->cache               = taggedCache(JobCountersCacheKeys::TAG_KEY);
        $this->listingDataProvider = $listingDataProvider;
    }

    /**
     * Recalculate inbox counter.
     */
    public function recalculatedInboxCounter(): void
    {
        $this->cache->forget(JobCountersCacheKeys::INBOX_KEY);
        $this->getInboxCounter();
    }

    /**
     * Recalculates job counters.
     *
     * @param array $userIds Array of user ids.
     */
    public function recalculateUsersCounters(array $userIds = []): void
    {
        if (empty($userIds)) {
            return;
        }

        foreach ($userIds as $userId) {
            if (null === $userId) {
                continue;
            }

            $cacheKey = sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $userId);
            $this->cache->forget($cacheKey);
            $this->getMineCounters($userId);
        }
    }

    /**
     * Recalculate all teams counters.
     *
     * @param array $teamIds Array of team ids.
     */
    public function recalculateTeamsCounters(array $teamIds = []): void
    {
        if (empty($teamIds)) {
            return;
        }

        foreach ($teamIds as $teamId) {
            if (null === $teamId) {
                continue;
            }

            $cacheKey = sprintf(JobCountersCacheKeys::TEAMS_KEY_PATTERN, $teamId);
            $this->cache->forget($cacheKey);
            $this->getTeamCounters($teamId);
        }
    }

    /**
     * Recalculate all counters.
     *
     * @param array $userIds Array of user ids.
     * @param array $teamIds Array of team ids.
     */
    public function recalculateAllCounters(array $userIds = [], array $teamIds = []): void
    {
        $this->recalculatedInboxCounter();
        $this->recalculateUsersCounters($userIds);
        $this->recalculateTeamsCounters($teamIds);
    }

    /**
     * Returns counter for "Upcoming KPI" folder.
     *
     * @param int $userId User id.
     *
     * @return int
     */
    public function getUpcomingKpiCounter(int $userId): int
    {
        return $this->listingDataProvider
            ->getUpcomingKpiQuery($userId)
            ->count();
    }

    /**
     * Returns count of jobs for folder "No contact 24 hours".
     *
     * @param int $userId User id.
     *
     * @return int
     */
    public function getNoContact24HoursCounter(int $userId): int
    {
        return $this->listingDataProvider
            ->getNoContact24HoursQuery($userId)
            ->count();
    }

    /**
     * Get inbox counter from cache or from db.
     *
     * @return int
     */
    public function getInboxCounter(): int
    {
        $inboxCounter = $this->cache->get(JobCountersCacheKeys::INBOX_KEY);

        if (empty($inboxCounter)) {
            $inboxCounter = $this->listingDataProvider->getInboxQuery()
                ->count();
            $this->cache->put(
                JobCountersCacheKeys::INBOX_KEY,
                $inboxCounter,
                Carbon::now()->addMinutes(JobCountersCacheKeys::TTL_IN_MINUTES)
            );
        }

        return (int)$inboxCounter;
    }

    /**
     * Returns mine counters: mine, active, closed with teams by user id.
     *
     * @param int $userId User id.
     *
     * @return array|mixed
     */
    public function getMineCounters(int $userId): array
    {
        $cacheKey = sprintf(JobCountersCacheKeys::MINE_KEY_PATTERN, $userId);

        $cached = $this->cache->get($cacheKey);

        $result = null;
        if (!empty($cached)) {
            $result = json_decode($cached, true);
        }

        if (null === $result) {
            $result = [
                'mine'   => $this->listingDataProvider->getMineQuery($userId)->count(),
                'active' => $this->listingDataProvider->getActiveQuery($userId)->count(),
                'closed' => $this->listingDataProvider->getClosedQuery($userId)->count(),
            ];

            $this->cache->put(
                $cacheKey,
                json_encode($result),
                Carbon::now()->addMinutes(JobCountersCacheKeys::TTL_IN_MINUTES)
            );
        }
        $result['teams'] = $this->getUserTeamsCounters($userId);

        return $result;
    }

    /**
     * Returns team with assigned jobs count.
     *
     * @param int $teamId Team id.
     *
     * @return array
     */
    public function getTeamCounters(int $teamId): array
    {
        $cacheKey = sprintf(JobCountersCacheKeys::TEAMS_KEY_PATTERN, $teamId);

        $cached = $this->cache->get($cacheKey);

        $result = null;
        if (!empty($cached)) {
            $result = json_decode($cached, true);
        } else {
            $result = $this->listingDataProvider->getTeam($teamId);

            $this->cache->put(
                $cacheKey,
                json_encode($result),
                Carbon::now()->addMinutes(JobCountersCacheKeys::TTL_IN_MINUTES)
            );
        }

        return json_decode(json_encode($result), true);
    }

    /**
     * Returns user teams with job counters.
     *
     * @param int $userId User id.
     *
     * @return array
     */
    public function getUserTeamsCounters(int $userId): array
    {
        $usersService = app()->make(UsersServiceInterface::class);

        $userTeams = $usersService->getTeams($userId);

        if ($userTeams->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($userTeams as $userTeam) {
            $result[] = $this->getTeamCounters($userTeam['id']);
        }

        return $result;
    }
}
