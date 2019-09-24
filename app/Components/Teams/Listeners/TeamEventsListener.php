<?php

namespace App\Components\Teams\Listeners;

use App\Components\Jobs\Interfaces\JobListingServiceInterface;
use App\Components\Teams\Events\UserAssignedToTeam;
use App\Components\Teams\Events\UserUnassignedFromTeam;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class TeamEventsListener
 *
 * @package Components\Jobs\Listeners
 */
class TeamEventsListener
{
    /** @var JobListingServiceInterface $jobListingService */
    private $jobListingService;

    /**
     * TeamEventsListener constructor.
     *
     * @param \App\Components\Jobs\Interfaces\JobListingServiceInterface $jobListingService
     */
    public function __construct(JobListingServiceInterface $jobListingService)
    {
        $this->jobListingService = $jobListingService;
    }

    /**
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(
            UserAssignedToTeam::class,
            self::class . '@onAssignedToTeam'
        );

        $events->listen(
            UserUnassignedFromTeam::class,
            self::class . '@onUnassignedFromTeam'
        );
    }

    /**
     * @param \App\Components\Teams\Events\UserAssignedToTeam $event
     */
    public function onAssignedToTeam(UserAssignedToTeam $event): void
    {
        $this->jobListingService->recalculateUsersCounters([$event->userId]);
    }

    /**
     * @param \App\Components\Teams\Events\UserUnassignedFromTeam $event
     */
    public function onUnassignedFromTeam(UserUnassignedFromTeam $event): void
    {
        $this->jobListingService->recalculateUsersCounters([$event->userId]);
    }
}
