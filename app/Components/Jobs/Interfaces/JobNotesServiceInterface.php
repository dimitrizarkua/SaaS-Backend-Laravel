<?php

namespace App\Components\Jobs\Interfaces;

/**
 * Interface JobNotesServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobNotesServiceInterface
{
    /**
     * Add a note to a job. Optionally allows to change job status.
     *
     * @param int    $jobId     Job id.
     * @param int    $noteId    Note id.
     * @param string $status    New job status.
     * @param bool   $mergeable Should note be merged while merging
     *
     * @see \App\Components\Jobs\Enums\JobStatuses
     */
    public function addNote(int $jobId, int $noteId, string $status = null, bool $mergeable = true): void;

    /**
     * Allows to remove a note from a job.
     *
     * @param int $jobId  Job id.
     * @param int $noteId Note id.
     */
    public function removeNote(int $jobId, int $noteId): void;
}
