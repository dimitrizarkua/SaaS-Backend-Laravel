<?php

namespace App\Components\Jobs\Models\VO;

use App\Components\Jobs\Interfaces\JobCountersInterface;
use App\Core\JsonModel;
use Illuminate\Support\Collection;

/**
 * Class JobCounters
 *
 * @package App\Components\Jobs\Models\VO
 * @method static JobCounters createFromJson($data = null, $target = null, bool $exceptionOnMissingData = true)
 */
class JobCounters extends JsonModel implements JobCountersInterface
{
    /**
     * @var int
     */
    public $inbox = 0;
    /**
     * @var int
     */
    public $mine = 0;
    /**
     * @var int
     */
    public $active = 0;
    /**
     * @var int
     */
    public $closed = 0;
    /**
     * @var \App\Components\Jobs\Models\VO\TeamCounter[]
     */
    public $teams = [];
    /**
     * @var int
     */
    public $no_contact_24_hours = 0;
    /**
     * @var int
     */
    public $upcoming_kpi = 0;

    /**
     * Returns jobs count for "Inbox" tab.
     *
     * @return int
     */
    public function getInboxCount(): int
    {
        return $this->inbox;
    }

    /**
     * Returns jobs count for "Mine" tab.
     *
     * @return int
     */
    public function getMineCount(): int
    {
        return $this->mine;
    }

    /**
     * Returns jobs count for "My team" tab.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTeams(): Collection
    {
        return Collection::make($this->teams);
    }

    /**
     * Returns jobs count for "All active Jobs" tab.
     *
     * @return int
     */
    public function getAllActiveJobsCount(): int
    {
        return $this->active;
    }

    /**
     * Returns jobs count for "Closed" tab.
     *
     * @return int
     */
    public function getClosedCount(): int
    {
        return $this->closed;
    }

    /**
     * @inheritDoc
     */
    public function getNoContact24HoursCount(): int
    {
        return $this->no_contact_24_hours;
    }

    /**
     * @inheritDoc
     */
    public function getUpcomingKPICount(): int
    {
        return $this->upcoming_kpi;
    }
}
