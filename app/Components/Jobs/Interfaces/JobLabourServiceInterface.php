<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\VO\JobLabourData;

/**
 * Interface JobLabourServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobLabourServiceInterface
{
    /**
     * Creates new job labour.
     *
     * @param \App\Components\Jobs\Models\VO\JobLabourData $data New job labour data.
     *
     * @return \App\Components\Jobs\Models\JobLabour
     *
     * @throws \Throwable
     */
    public function createJobLabour(JobLabourData $data): JobLabour;

    /**
     * Updates existing job labour.
     *
     * @param \App\Components\Jobs\Models\JobLabour        $jobLabour Updating JobLabour entity.
     * @param \App\Components\Jobs\Models\VO\JobLabourData $data      Updated job labour data.
     *
     * @return \App\Components\Jobs\Models\JobLabour
     *
     * @throws \Throwable
     */
    public function updateJobLabour(JobLabour $jobLabour, JobLabourData $data): JobLabour;

    /**
     * Deletes existing job labour.
     *
     * @param \App\Components\Jobs\Models\JobLabour $jobLabour Deleting JobLabour entity.
     *
     * @throws \Throwable
     */
    public function deleteJobLabour(JobLabour $jobLabour): void;

    /**
     * Calculates total and overridden total amounts of materials entries for specific job based on insurer contract
     * with up_to_amount and up_to_hours restrictions without taxes.
     *
     * @param int $jobId Identifier job for which the total amount is calculated.
     *
     * @return float
     *
     * @throws \Throwable
     */
    public function calculateTotalAmountByJob(int $jobId): float;
}
