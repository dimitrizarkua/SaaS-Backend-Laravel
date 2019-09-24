<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobDocumentsServiceInterface;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Class JobDocumentsService
 *
 * @package App\Components\Jobs\Services
 */
class JobDocumentsService extends JobsEntityService implements JobDocumentsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function attachDocument(int $jobId, int $documentId, string $type = null): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            DB::transaction(function () use (&$job, $documentId, $type) {
                $job->documents()->attach($documentId, ['type' => $type]);
                $job->updateTouchedAt();
            });
        } catch (Exception $exception) {
            throw new NotAllowedException('This document is already attached to specified job.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function detachDocument(int $jobId, int $documentId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->documents()->detach($documentId);
    }
}
