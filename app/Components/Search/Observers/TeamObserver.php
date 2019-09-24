<?php

namespace App\Components\Search\Observers;

use App\Components\Search\Models\UserAndTeam;
use App\Components\Teams\Models\Team;

/**
 * Class TeamObserver
 *
 * @package App\Http\Requests\Search\Observers
 */
class TeamObserver
{
    /**
     * Handle the Team "created" event.
     *
     * @param  Team $team
     *
     * @return void
     */
    public function created(Team $team)
    {
        $this->updateIndex($team);
    }

    /**
     * Handle the Team "updated" event.
     *
     * @param  Team $team
     *
     * @return void
     */
    public function updated(Team $team)
    {
        $this->updateIndex($team);
    }

    /**
     * Handle the Team "deleting" event.
     *
     * @param  Team $team
     *
     * @return void
     */
    public function deleting(Team $team)
    {
        $this->getTeamQuery($team)->unsearchable();
    }

    /**
     * Updates index.
     *
     * @param Team $team
     */
    private function updateIndex(Team $team): void
    {
        $this->getTeamQuery($team)->searchable();
    }

    /**
     * Returns query to get user record from view.
     *
     * @param Team $team
     *
     * @return \App\Components\Search\Models\UserAndTeam
     */
    private function getTeamQuery(Team $team)
    {
        return UserAndTeam::where('type', UserAndTeam::TYPE_TEAM)
            ->where('id', $team->id);
    }
}
