<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\VO\CreateJobEquipmentData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Interface JobEquipmentServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobEquipmentServiceInterface
{
    /**
     * Returns specified job equipment by id.
     *
     * @param int $jobEquipmentId JobEquipment id.
     *
     * @return JobEquipment
     */
    public function getJobEquipment(int $jobEquipmentId): JobEquipment;

    /**
     * Returns list of all job equipment related to the specified job.
     *
     * @param int $jobId Job id.
     *
     * @return Collection
     */
    public function getJobEquipmentList(int $jobId): Collection;

    /**
     * Creates new job equipment.
     *
     * @param CreateJobEquipmentData $data   Job equipment data.
     * @param int                    $jobId  Job identifier.
     * @param int                    $userId Identifier of user who is creating the job equipment.
     *
     * @return JobEquipment
     */
    public function createJobEquipment(CreateJobEquipmentData $data, int $jobId, int $userId): JobEquipment;

    /**
     * Updates ended at for job equipment.
     *
     * @param int    $jobEquipmentId Job equipment identifier.
     * @param Carbon $endedAt        Date-time when job equipment is no longer used.
     *
     * @return JobEquipment
     */
    public function finishJobEquipmentUsing(int $jobEquipmentId, Carbon $endedAt): JobEquipment;

    /**
     * Updates intervals count override for job equipment.
     *
     * @param int $jobEquipmentId Job equipment identifier.
     * @param int $count          Job equipment intervals count override.
     *
     * @return JobEquipment
     */
    public function overrideJobEquipmentIntervalsCount(int $jobEquipmentId, int $count): JobEquipment;

    /**
     * Removes job equipment.
     *
     * @param int $jobEquipmentId Job equipment identifier.
     *
     * @return void
     */
    public function deleteJobEquipment(int $jobEquipmentId): void;

    /**
     * Returns equipment costing information for specified job.
     *
     * @param int $jobId Job id.
     *
     * @return array
     */
    public function getJobEquipmentTotalAmount(int $jobId): array;
}
