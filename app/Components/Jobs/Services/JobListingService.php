<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\DataProviders\JobCountersDataProvider;
use App\Components\Jobs\DataProviders\JobListingsDataProvider;
use App\Components\Jobs\Interfaces\JobCountersInterface;
use App\Components\Jobs\Interfaces\JobListingServiceInterface;
use App\Components\Jobs\Models\VO\JobCounters;
use Illuminate\Support\Collection;

/**
 * Class JobListingService
 *
 * @package App\Components\Jobs\Services
 */
class JobListingService implements JobListingServiceInterface
{
    /**
     * @var JobListingsDataProvider
     */
    private $listingDataProvider;

    /**
     * @var JobCountersDataProvider
     */
    private $counterDataProvider;

    /**
     * JobListingService constructor.
     *
     */
    public function __construct(
        JobListingsDataProvider $listingDataProvider,
        JobCountersDataProvider $counterDataProvider
    ) {
        $this->listingDataProvider = $listingDataProvider;
        $this->counterDataProvider = $counterDataProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \JsonMapper_Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getCountersAndTeams(int $userId): JobCountersInterface
    {
        $mineCounters = $this->counterDataProvider->getMineCounters($userId);

        $data['inbox'] = $this->counterDataProvider->getInboxCounter();
        $data['no_contact_24_hours'] = $this->counterDataProvider->getNoContact24HoursCounter($userId);
        $data['upcoming_kpi'] = $this->counterDataProvider->getUpcomingKpiCounter($userId);
        $data = array_merge($data, $mineCounters);

        return JobCounters::createFromJson($data);
    }

    /**
     * @inheritDoc
     */
    public function getUpcomingKpi(int $userId): Collection
    {
        return $this->listingDataProvider->getUpcomingKpiQuery($userId)
            ->get();
    }

    /**
     * Returns list of jobs for folder "No contact 24 hours".
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getNoContact24Hours(int $userId): Collection
    {
        return $this->listingDataProvider->getNoContact24HoursQuery($userId)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getInbox(): Collection
    {
        return $this->listingDataProvider->getInboxQuery()
            ->with('nextTask')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getLocal(int $userId): Collection
    {
        return $this->listingDataProvider->getLocalQuery($userId)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getMine(int $userId): Collection
    {
        return $this->listingDataProvider->getMineQuery($userId)
            ->with('nextTask')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getByTeam(int $teamId): Collection
    {
        return $this->listingDataProvider->getByTeam($teamId)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getActive(int $userId): Collection
    {
        return $this->listingDataProvider->getActiveQuery($userId)
            ->with('nextTask')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getClosed(int $userId): Collection
    {
        return $this->listingDataProvider
            ->getClosedQuery($userId)
            ->with('nextTask')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function recalculateInboxCounter(): void
    {
        $this->counterDataProvider->recalculatedInboxCounter();
    }

    /**
     * {@inheritdoc}
     */
    public function recalculateUsersCounters(array $userIds = []): void
    {
        $this->counterDataProvider->recalculateUsersCounters($userIds);
    }

    /**
     * {@inheritdoc}
     */
    public function recalculateTeamsCounters(array $teamIds = []): void
    {
        $this->counterDataProvider->recalculateTeamsCounters($teamIds);
    }

    /**
     * {@inheritdoc}
     */
    public function recalculateAllCounters(array $userIds = [], array $teamIds = []): void
    {
        $this->counterDataProvider->recalculateAllCounters($userIds, $teamIds);
    }
}
