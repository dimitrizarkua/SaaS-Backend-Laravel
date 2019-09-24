<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskScheduledPortionStatus;
use App\Components\Jobs\Models\JobTaskStatus;
use App\Components\Jobs\Models\VO\JobTaskData;
use Illuminate\Support\Collection;

/**
 * Interface JobTasksServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobTasksServiceInterface
{
    /**
     * Get job task by id.
     *
     * @param int $taskId
     *
     * @return \App\Components\Jobs\Models\JobTask
     */
    public function getTask(int $taskId): JobTask;

    /**
     * Get all tasks related to the specified job.
     *
     * @param int $jobId Job id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listJobTasks(int $jobId): Collection;

    /**
     * Get all unscheduled tasks related to the specified location.
     *
     * @param int $locationId Location id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listUnscheduledLocationTasks(int $locationId): Collection;

    /**
     * Get all tasks assigned to a user.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUserTasks(int $userId): Collection;

    /**
     * Create new job task.
     *
     * @param \App\Components\Jobs\Models\VO\JobTaskData $data   Job task data.
     * @param int                                        $jobId  Job identifier.
     * @param int|null                                   $userId User who is creating the task.
     *
     * @return \App\Components\Jobs\Models\JobTask
     */
    public function createTask(JobTaskData $data, int $jobId, int $userId = null): JobTask;

    /**
     * Delete job task.
     *
     * @param int $taskId Job task id.
     *
     * @return void
     */
    public function deleteTask(int $taskId): void;

    /**
     * Change status of a job task.
     *
     * @param int    $taskId Job task id.
     * @param string $status Task status.
     *
     * @return \App\Components\Jobs\Models\JobTaskStatus
     */
    public function changeStatus(int $taskId, string $status): JobTaskStatus;

    /**
     * Snooze a task.
     *
     * @param int    $taskId      Job task id.
     * @param string $snoozeUntil Snooze until date.
     */
    public function snoozeTask(int $taskId, string $snoozeUntil);

    /**
     * Unsnooze a task.
     *
     * @param int $taskId Job task id.
     */
    public function unsnoozeTask(int $taskId);

    /**
     * Change scheduled portion of a task's status.
     *
     * @param int    $taskId Job task id.
     * @param string $status Task status.
     *
     * @return \App\Components\Jobs\Models\JobTaskScheduledPortionStatus
     */
    public function changeScheduledStatus(int $taskId, string $status): JobTaskScheduledPortionStatus;

    /**
     * Assign user to a task's crew.
     *
     * @param int $taskId Job task id.
     * @param int $userId User id.
     *
     * @return void
     */
    public function assignUser(int $taskId, int $userId): void;

    /**
     * Unassign user from a task's crew.
     *
     * @param int $taskId Job task id.
     * @param int $userId User id.
     *
     * @return void
     */
    public function unassignUser(int $taskId, int $userId): void;

    /**
     * Assign vehicle to a task.
     *
     * @param int $taskId    Job task id.
     * @param int $vehicleId Vehicle id.
     *
     * @return void
     */
    public function assignVehicle(int $taskId, int $vehicleId): void;

    /**
     * Unassign vehicle from a task.
     *
     * @param int $taskId    Job task id.
     * @param int $vehicleId Vehicle id.
     *
     * @return void
     */
    public function unassignVehicle(int $taskId, int $vehicleId): void;

    /**
     * Assign team to a task.
     *
     * @param int $taskId Job task id.
     * @param int $teamId Team id.
     *
     * @return void
     */
    public function assignTeam(int $taskId, int $teamId): void;

    /**
     * Unassign team from a task.
     *
     * @param int $taskId Job task id.
     * @param int $teamId Team id.
     *
     * @return void
     */
    public function unassignTeam(int $taskId, int $teamId): void;
}
