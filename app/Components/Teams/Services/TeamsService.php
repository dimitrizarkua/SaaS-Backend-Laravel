<?php

namespace App\Components\Teams\Services;

use App\Components\Locations\Exceptions\NotAllowedException;
use App\Components\Teams\Events\UserAssignedToTeam;
use App\Components\Teams\Events\UserUnassignedFromTeam;
use App\Components\Teams\Interfaces\TeamsServiceInterface;
use App\Components\Teams\Models\Team;

/**
 * Class TeamsService
 *
 * @package App\Components\Teams\Services
 */
class TeamsService implements TeamsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getTeam(int $teamId): Team
    {
        return Team::findOrFail($teamId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     **/
    public function addUser(int $teamId, int $userId): void
    {
        $team = $this->getTeam($teamId);
        try {
            $team->users()->attach($userId);
        } catch (\Exception $exception) {
            throw new NotAllowedException('This user has been added earlier to this team.');
        }

        event(new UserAssignedToTeam($team, $userId));
    }

    /**
     * {@inheritdoc}
     **/
    public function removeUser(int $teamId, int $userId): void
    {
        $team = $this->getTeam($teamId);
        $team->users()->detach($userId);
        event(new UserUnassignedFromTeam($team, $userId));
    }
}
