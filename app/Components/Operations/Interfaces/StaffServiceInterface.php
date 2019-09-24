<?php

namespace App\Components\Operations\Interfaces;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface StaffServiceInterface
 *
 * @package App\Components\Operations\Interfaces
 */
interface StaffServiceInterface
{
    /**
     * Search for staff.
     *
     * @param int                        $locationId Location identifier.
     * @param \Illuminate\Support\Carbon $date       Date on which the list is made.
     * @param string                     $name       User name to search.
     * @param int                        $limit      Number of results.
     *
     * @return \Illuminate\Support\Collection|User[]
     */
    public function searchForStaff(int $locationId, Carbon $date, string $name, int $limit = 10): Collection;

    /**
     * List of staff with work hours filtered by location.
     *
     * @param \Illuminate\Support\Carbon $date       Date on which the list is made.
     * @param int                        $locationId Location identifier.
     *
     * @return \Illuminate\Support\Collection|User[]
     */
    public function listLocationStaff(Carbon $date, int $locationId): Collection;

    /**
     * Get specified staff with work hours filtered by location.
     *
     * @param int                        $userId Staff identifier.
     * @param \Illuminate\Support\Carbon $date   Date on which the list is made.
     *
     * @return User
     */

    public function getStaff(int $userId, Carbon $date): User;

    /**
     * Returns staff list with work hours fields.
     *
     * @param \Illuminate\Support\Collection $users List of staff.
     * @param \Illuminate\Support\Carbon     $date  Date on which the list is made.
     *
     * @return \Illuminate\Support\Collection|User[]
     */
    public function getUsersWithWorkHours(Collection $users, Carbon $date): Collection;
}
