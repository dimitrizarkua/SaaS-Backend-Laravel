<?php

namespace App\Components\Users\Services;

use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Components\Users\Exceptions\NotAllowedException;
use App\Components\Users\Interfaces\UserProfileServiceInterface;
use App\Models\User;
use App\Models\VO\UpdateUserData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Class UserProfileService
 *
 * @package App\Components\Users\Services
 */
class UserProfileService implements UserProfileServiceInterface
{
    /** @var array User avatar thumbnails [width x height] */
    public const THUMB_SIZES = [
        [50, 50],
        [100, 100],
    ];

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getUser(int $userId): User
    {
        return User::findOrFail($userId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function updateAvatar(int $userId, UploadedFile $photo): User
    {
        $user = $this->getUser($userId);

        $photoService = $this->getPhotosService();
        if ($user->avatar_photos_id) {
            $photoService->updatePhotoFromFile($user->avatar_photos_id, $photo);
        } else {
            DB::transaction(function () use ($user, $photo, $photoService) {
                $avatar                 = $photoService->createPhotoFromFile($photo);
                $user->avatar_photos_id = $avatar->id;
                $user->saveOrFail();

                foreach (self::THUMB_SIZES as $size) {
                    $photoService->generateThumbnail($avatar->id, ...$size);
                }
            });
        }

        $user->refresh();

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function deleteAvatar(int $userId): void
    {
        $user = $this->getUser($userId);
        if (!$user->avatar_photos_id) {
            throw new NotAllowedException('Avatar not set.');
        }

        $photoService = $this->getPhotosService();
        DB::transaction(function () use ($user, $photoService) {
            $avatarId = $user->avatar_photos_id;

            $user->avatar_photos_id = null;
            $user->saveOrFail();

            try {
                $photoService->deletePhoto($avatarId);
            } catch (\App\Components\Photos\Exceptions\NotAllowedException $exception) {
                // Perhaps photo is still attached to some other entity.
                // Do nothing in this case.
            }
        });
    }

    /**
     * @return \App\Components\Photos\Interfaces\PhotosServiceInterface
     */
    private function getPhotosService(): PhotosServiceInterface
    {
        return app()->make(PhotosServiceInterface::class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function updateUser(int $userId, UpdateUserData $data): User
    {
        $user = $this->getUser($userId);

        if (null !== $data->getPassword()) {
            $user->setPassword($data->getPassword());
        }

        $user->update($data->toArray());

        $locations = $data->getLocations();
        if ($locations) {
            DB::transaction(function () use ($user, $locations) {
                $user->locations()->detach();
                foreach ($locations as [$locationId, $isPrimary]) {
                    $user->locations()->attach($locationId, ['primary' => $isPrimary]);
                }
            });
        }

        return $user->refresh();
    }
}
