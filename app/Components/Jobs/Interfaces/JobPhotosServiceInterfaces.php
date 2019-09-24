<?php

namespace App\Components\Jobs\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface JobPhotosServiceInterfaces
 *
 * @package App\Components\Jobs\Interfaces
 */
interface JobPhotosServiceInterfaces
{
    /**
     * List all photos attached to a job.
     *
     * @param int $jobId Job id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listJobPhotos(int $jobId): Collection;

    /**
     * Allows to attach a photo to a job.
     *
     * @param int    $jobId       Job id.
     * @param int    $photoId     Photo id.
     * @param int    $creatorId   Id of the user who attaching the photo.
     * @param string $description Photo description.
     *
     * @return void
     */
    public function attachPhoto(int $jobId, int $photoId, int $creatorId, string $description): void;

    /**
     * Allows to detach a photo from a job.
     *
     * @param int $jobId   Job id.
     * @param int $photoId Photo id.
     *
     * @return void
     */
    public function detachPhoto(int $jobId, int $photoId): void;

    /**
     * Allows to detach multiple photos from a job.
     *
     * @param int   $jobId    Job id.
     * @param array $photoIds Photos identifiers.
     *
     * @return void
     */
    public function detachPhotos(int $jobId, array $photoIds): void;

    /**
     * Allows to change description of an attached photo.
     *
     * @param int    $jobId       Job id.
     * @param int    $photoId     Photo id.
     * @param int    $userId      Id of a user who modifies the photo.
     * @param string $description Photo description.
     *
     * @return void
     */
    public function updateDescription(int $jobId, int $photoId, int $userId, string $description): void;
}
