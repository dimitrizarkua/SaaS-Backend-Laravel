<?php

namespace App\Components\Jobs\Services;

use App\Components\Jobs\Exceptions\NotAllowedException;
use App\Components\Jobs\Interfaces\JobPhotosServiceInterfaces;
use App\Components\Jobs\Models\JobPhoto;
use App\Components\Photos\Interfaces\PhotosServiceInterface;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class JobPhotosService
 *
 * @package App\Components\Jobs\Services
 */
class JobPhotosService extends JobsEntityService implements JobPhotosServiceInterfaces
{
    /** @var array Job thumbnail sizes [width x height] */
    const THUMB_SIZES = [
        [300, 300],
        [412, 412],
    ];

    /**
     * {@inheritdoc}
     */
    public function listJobPhotos(int $jobId): Collection
    {
        return JobPhoto::query()
            ->where('job_id', '=', $jobId)
            ->with('photo')
            ->with('photo.thumbnails')
            ->with('creator')
            ->with('modifiedBy')
            ->get();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function attachPhoto(int $jobId, int $photoId, int $creatorId = null, string $description = null): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        try {
            $attributes = [
                'creator_id'  => $creatorId,
                'description' => $description,
            ];

            $job->photos()->attach($photoId, $attributes);

            $photoService = $this->getPhotosService();
            foreach (self::THUMB_SIZES as $size) {
                $photoService->generateThumbnail($photoId, ...$size);
            }
        } catch (Exception $exception) {
            throw new NotAllowedException('This photo is already attached to the job.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function detachPhoto(int $jobId, int $photoId): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->photos()->detach($photoId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function detachPhotos(int $jobId, array $photoIds): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $job->photos()->detach($photoIds);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function updateDescription(int $jobId, int $photoId, int $userId, string $description): void
    {
        $job = $this->jobsService()->getJob($jobId);
        if ($job->isClosed()) {
            throw new NotAllowedException('Could not make changes to the closed or cancelled job.');
        }

        $jobPhoto = JobPhoto::where('job_id', '=', $jobId)
            ->where('photo_id', '=', $photoId)
            ->first();

        if ($jobPhoto) {
            $jobPhoto->update([
                'modified_by_id' => $userId,
                'description'    => $description,
            ]);
        }
    }

    /**
     * @return \App\Components\Photos\Interfaces\PhotosServiceInterface
     */
    private function getPhotosService(): PhotosServiceInterface
    {
        return app()->make(PhotosServiceInterface::class);
    }
}
