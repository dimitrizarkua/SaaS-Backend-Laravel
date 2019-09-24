<?php

namespace App\Components\Users\Interfaces;

use App\Models\User;
use App\Models\VO\UpdateUserData;
use Illuminate\Http\UploadedFile;

/**
 * Interface UserProfileServiceInterface
 *
 * @package App\Components\Users\Interfaces
 */
interface UserProfileServiceInterface
{
    /**
     * Get user by id.
     *
     * @param int $userId User id.
     *
     * @return \App\Models\User
     */
    public function getUser(int $userId): User;

    /**
     * Allows user to upload new / update existing avatar.
     *
     * @param int                           $userId User id.
     * @param \Illuminate\Http\UploadedFile $photo  Uploaded photo.
     *
     * @return \App\Models\User
     */
    public function updateAvatar(int $userId, UploadedFile $photo): User;

    /**
     * Allows user to delete existing avatar.
     *
     * @param int $userId User id.
     */
    public function deleteAvatar(int $userId);

    /**
     * Updates user.
     *
     * @param int            $userId User identifier.
     * @param UpdateUserData $data   Update user data.
     *
     * @return User
     */
    public function updateUser(int $userId, UpdateUserData $data): User;
}
