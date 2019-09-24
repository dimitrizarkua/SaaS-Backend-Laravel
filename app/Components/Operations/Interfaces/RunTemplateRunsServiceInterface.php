<?php

namespace App\Components\Operations\Interfaces;

use App\Components\Operations\Models\JobRunTemplateRun;
use Illuminate\Support\Collection;

/**
 * Interface RunTemplateRunsServiceInterface
 *
 * @package App\Components\Operations\Interfaces
 */
interface RunTemplateRunsServiceInterface
{
    /**
     * Get a template run by id.
     *
     * @param int $runId Template run id.
     *
     * @return \App\Components\Operations\Models\JobRunTemplateRun
     */
    public function getTemplateRun(int $runId): JobRunTemplateRun;

    /**
     * Get all template's runs.
     *
     * @param int $templateId Template id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listTemplateRuns(int $templateId): Collection;

    /**
     * Create new template run.
     *
     * @param int         $templateId Template id.
     * @param string|null $name       Run name.
     *
     * @return \App\Components\Operations\Models\JobRunTemplateRun
     */
    public function createTemplateRun(int $templateId, string $name = null): JobRunTemplateRun;

    /**
     * Delete existing template run.
     *
     * @param int $runId Run id.
     *
     * @return void
     */
    public function deleteTemplateRun(int $runId): void;

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
}
