<?php

namespace App\Components\Jobs\Models\VO;

use App\Components\Jobs\Interfaces\TeamWithJobsCounterInterface;
use App\Core\JsonModel;

/**
 * Class TeamCounter
 *
 * @package App\Components\Jobs\Models\VO
 */
class TeamCounter extends JsonModel implements TeamWithJobsCounterInterface
{
    /**
     * Team id.
     *
     * @var int
     */
    public $id;
    /**
     * Team name.
     *
     * @var string
     */
    public $name;
    /**
     * Count of jobs for the team.
     *
     * @var int
     */
    public $jobs_count = 0;

    /**
     * Returns team id.
     *
     * @return int
     */
    public function getTeamId(): int
    {
        return $this->id;
    }

    /**
     * Returns team name.
     *
     * @return string
     */
    public function getTeamName(): string
    {
        return $this->name;
    }

    /**
     * Returns count of jobs assigned to the team.
     *
     * @return int
     */
    public function getJobsCount(): int
    {
        return $this->jobs_count;
    }
}
