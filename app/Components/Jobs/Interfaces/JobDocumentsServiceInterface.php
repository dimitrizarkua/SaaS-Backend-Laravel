<?php

namespace App\Components\Jobs\Interfaces;

/**
 * Interface JobDocumentsServiceInterface
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobDocumentsServiceInterface
{
    /**
     * Allows to attach a document to a job.
     *
     * @param int    $jobId      Job id.
     * @param int    $documentId Document id.
     * @param string $type       Document type
     */
    public function attachDocument(int $jobId, int $documentId, string $type = null): void;

    /**
     * Allows to detach a document from a job.
     *
     * @param int $jobId      Job id.
     * @param int $documentId Document id.
     */
    public function detachDocument(int $jobId, int $documentId): void;
}
