<?php

namespace App\Components\Jobs\Interfaces;

/**
 * Interface JobTagsServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobTagsServiceInterface
{
    /**
     * Allows to assign a tag to a job.
     *
     * @param int $jobId Job id.
     * @param int $tagId Tag id.
     */
    public function assignTag(int $jobId, int $tagId): void;

    /**
     * Allows to unassign a tag from a job.
     *
     * @param int $jobId Job id.
     * @param int $tagId Tag id.
     */
    public function unassignTag(int $jobId, int $tagId): void;
}
