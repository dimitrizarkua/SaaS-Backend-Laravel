<?php

namespace App\Components\Jobs\Interfaces;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\VO\JobCreationData;
use Illuminate\Support\Collection;

/**
 * Interface JobsServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobsServiceInterface
{
    /**
     * Creates new job.
     *
     * @param JobCreationData|null $data
     * @param string               $jobStatus  Status of created job.
     * @param int|null             $userId     Id of user who created the job.
     * @param bool                 $autoAssign Shows whether the job should be assigned to $userId automatically.
     *
     * @return \App\Components\Jobs\Models\Job
     * @throws \Throwable
     */
    public function createJob(
        JobCreationData $data = null,
        string $jobStatus = JobStatuses::NEW,
        int $userId = null,
        bool $autoAssign = true
    ): Job;

    /**
     * Updates job.
     *
     * @param \App\Components\Jobs\Models\Job $job
     * @param array                           $data
     *
     * @return \App\Components\Jobs\Models\Job
     */
    public function updateJob(Job $job, array $data): Job;

    /**
     * Removes a job.
     *
     * @param \App\Components\Jobs\Models\Job $job    Job which will be deleted.
     * @param int|null                        $userId Id of user who deleted the job.
     *
     * @return void
     */
    public function deleteJob(Job $job, int $userId = null): void;

    /**
     * Returns job by id.
     *
     * @param int $jobId Job id.
     *
     * @return \App\Components\Jobs\Models\Job
     */
    public function getJob(int $jobId): Job;

    /**
     * Convenience method that returns current job status by its id.
     *
     * @param int $jobId Job id.
     *
     * @return string
     */
    public function getJobStatus(int $jobId): string;

    /**
     * Allows to pin or un-pin a job.
     *
     * @param int  $jobId Job id.
     * @param bool $value Job is pinned if true, false otherwise.
     */
    public function pin(int $jobId, bool $value = true): void;

    /**
     * Allows to "touch" a job, i.e. indicate that a job was updated in some way.
     *
     * @param int $jobId Job id.
     */
    public function touch(int $jobId): void;

    /**
     * Links one job to another in both directions.
     *
     * @param int $jobId       Source job id.
     * @param int $linkedJobId Destination job id.
     */
    public function linkJobs(int $jobId, int $linkedJobId): void;

    /**
     * Unlinks one job from another in both directions.
     *
     * @param int $jobId       Source job id.
     * @param int $linkedJobId Destination job id.
     */
    public function unlinkJobs(int $jobId, int $linkedJobId): void;

    /**
     * Returns list of linked jobs for specified job.
     *
     * @param int $jobId Job id.
     *
     * @return \Illuminate\Support\Collection.
     */
    public function getLinkedJobs(int $jobId): Collection;

    /**
     * Allows to snooze a job.
     *
     * @param int    $jobId Job id.
     * @param string $date  Date until which the job is snoozed.
     */
    public function snoozeJob(int $jobId, string $date): void;

    /**
     * Allows to un-snooze a job.
     *
     * @param int $jobId Job id.
     */
    public function unsnoozeJob(int $jobId): void;

    /**
     * Returns usage & actuals counters for a job.
     *
     * @param int $jobId Job identifier.
     *
     * @return array
     */
    public function getJobCostingCounters(int $jobId): array;
}
