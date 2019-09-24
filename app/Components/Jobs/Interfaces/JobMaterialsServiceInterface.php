<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\VO\JobMaterialData;

/**
 * Interface JobMaterialsServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobMaterialsServiceInterface
{
    /**
     * @param \App\Components\Jobs\Models\VO\JobMaterialData $data
     *
     * @return \App\Components\Jobs\Models\JobMaterial
     *
     * @throws \Throwable
     */
    public function create(JobMaterialData $data): JobMaterial;

    /**
     * @param \App\Components\Jobs\Models\JobMaterial        $jobMaterial
     * @param \App\Components\Jobs\Models\VO\JobMaterialData $data
     *
     * @return \App\Components\Jobs\Models\JobMaterial
     */
    public function update(JobMaterial $jobMaterial, JobMaterialData $data): JobMaterial;

    /**
     * @param \App\Components\Jobs\Models\JobMaterial $jobMaterial
     *
     * @throws \Exception
     */
    public function delete(JobMaterial $jobMaterial): void;

    /**
     * Calculate total and overridden total amounts of materials entries for specific job based on insurer contract
     * without taxes.
     *
     * @param int $jobId
     *
     * @return mixed
     */
    public function calculateTotalAmountByJob(int $jobId);
}
