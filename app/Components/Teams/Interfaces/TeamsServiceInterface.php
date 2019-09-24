<?php

namespace App\Components\Teams\Interfaces;

use App\Components\Teams\Models\Team;

/**
 * Interface TeamsServiceInterface
 *
 * @package App\Components\Teams\Interfaces
 */
interface TeamsServiceInterface
{
    /**
     * Returns team by id.
     *
     * @param int $teamId Team id.
     *
     * @return Team
     */
    public function getTeam(int $teamId): Team;

    /**
     * Attaches user to team.
     *
     * @param int $teamId
     * @param int $userId
     */
    public function addUser(int $teamId, int $userId): void;

    /**
     * Detaches user from team.
     *
     * @param int $teamId
     * @param int $userId
     */
    public function removeUser(int $teamId, int $userId): void;
}
