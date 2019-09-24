<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobTagsServiceInterface;
use Exception;

/**
 * Class JobTagsService
 *
 * @package App\Components\Jobs\Services
 */
class JobTagsService extends JobsEntityService implements JobTagsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     */
    public function assignTag(int $jobId, int $tagId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $job->tags()->attach($tagId);
        } catch (Exception $exception) {
            throw new NotAllowedException('This tag is already assigned to specified job.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function unassignTag(int $jobId, int $tagId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->tags()->detach($tagId);
    }
}
