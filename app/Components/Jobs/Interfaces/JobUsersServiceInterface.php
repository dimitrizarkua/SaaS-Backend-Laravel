<?php

namespace App\Components\Jobs\Interfaces;

/**
 * Interface JobUsersServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobUsersServiceInterface
{
    /**
     * Makes a user follower of a job.
     *
     * @param int $jobId  Job id.
     * @param int $userId User id.
     */
    public function follow(int $jobId, int $userId): void;

    /**
     * Remove user from job followers.
     *
     * @param int $jobId  Job id.
     * @param int $userId User id.
     */
    public function unfollow(int $jobId, int $userId): void;

    /**
     * Allows to assign a user to a job.
     *
     * @param int      $jobId  Job id.
     * @param int      $userId User id.
     * @param int|null $whoAssignedId
     */
    public function assignToUser(int $jobId, int $userId, int $whoAssignedId = null): void;

    /**
     * Allows to unassign a user from a job.
     *
     * @param int $jobId  Job id.
     * @param int $userId User id.
     */
    public function unassignFromUser(int $jobId, int $userId): void;

    /**
     *
     * Check if a user has assignment with a job.
     *
     * @param int $jobId  Job id.
     * @param int $userId User id.
     *
     * @return bool
     */
    public function isUserAssigned(int $jobId, int $userId): bool;

    /**
     * Allows to assign a team to a job.
     *
     * @param int $jobId  Job id.
     * @param int $teamId Team id.
     */
    public function assignToTeam(int $jobId, int $teamId): void;

    /**
     * Allows to unassign a team from a job.
     *
     * @param int $jobId  Job id.
     * @param int $teamId Team id.
     */
    public function unassignFromTeam(int $jobId, int $teamId): void;
}
