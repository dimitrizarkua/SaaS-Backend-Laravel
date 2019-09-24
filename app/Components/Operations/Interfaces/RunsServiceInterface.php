<?php

namespace App\Components\Operations\Interfaces;

use App\Components\Operations\Models\JobRun;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface RunsServiceInterface
 *
 * @package App\Components\Operations\Interfaces
 */
interface RunsServiceInterface
{
    /**
     * Get run by id.
     *
     * @param int $runId Run id.
     *
     * @return \App\Components\Operations\Models\JobRun
     */
    public function getRun(int $runId): JobRun;

    /**
     * Get all runs assigned to the specified location.
     *
     * @param int                        $locationId Location id.
     * @param \Illuminate\Support\Carbon $date       Requested date.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listLocationRuns(int $locationId, Carbon $date): Collection;

    /**
     * Create new run.
     *
     * @param int                        $locationId Location id.
     * @param \Illuminate\Support\Carbon $date       Run date.
     * @param string|null                $name       Run name.
     *
     * @return \App\Components\Operations\Models\JobRun
     */
    public function createRun(int $locationId, Carbon $date, string $name = null): JobRun;

    /**
     * Delete run.
     *
     * @param int $runId Run id.
     *
     * @return void
     */
    public function deleteRun(int $runId): void;

    /**
     * Assign a crew member to a run.
     *
     * @param int $runId  Run id.
     * @param int $userId User id.
     *
     * @return void
     */
    public function assignUser(int $runId, int $userId): void;

    /**
     * Unassign a crew member from a run.
     *
     * @param int $runId  Run id.
     * @param int $userId User id.
     *
     * @return void
     */
    public function unassignUser(int $runId, int $userId): void;

    /**
     * Assign a vehicle to a run.
     *
     * @param int $runId     Run id.
     * @param int $vehicleId Vehicle id.
     *
     * @return void
     */
    public function assignVehicle(int $runId, int $vehicleId): void;

    /**
     * Unassign crew member from a run.
     *
     * @param int $runId     Run id.
     * @param int $vehicleId Vehicle id.
     *
     * @return void
     */
    public function unassignVehicle(int $runId, int $vehicleId): void;

    /**
     * Schedule a task on specific time and add it to a run.
     *
     * @param int                        $runId    Run id.
     * @param int                        $taskId   Task id.
     * @param \Illuminate\Support\Carbon $startsAt Starts at date.
     * @param \Illuminate\Support\Carbon $endsAt   Ends at date.
     *
     * @return void
     */
    public function scheduleTask(int $runId, int $taskId, Carbon $startsAt, Carbon $endsAt): void;

    /**
     * Remove a task from a run.
     *
     * @param int $runId  Run id.
     * @param int $taskId Task id.
     *
     * @return void
     */
    public function removeTask(int $runId, int $taskId): void;

    /**
     * Create runs from a template on the specific date.
     *
     * @param int                        $templateId Template id.
     * @param \Illuminate\Support\Carbon $date       Requested date.
     *
     * @return Collection
     */
    public function createRunsFromTemplate(int $templateId, Carbon $date): Collection;
}
