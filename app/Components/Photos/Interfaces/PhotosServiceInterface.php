<?php

namespace App\Components\Photos\Interfaces;

use App\Components\Photos\Models\Photo;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface PhotosServiceInterface
 *
 * @package App\Components\Photos\Interfaces
 */
interface PhotosServiceInterface
{
    /**
     * Returns disk name to be used as a storage for photos.
     *
     * @return string
     */
    public function getDiskName(): string;

    /**
     * Allows to set the disk name.
     *
     * @param string $diskName Disk name.
     *
     * @return void
     */
    public function setDiskName(string $diskName): void;

    /**
     * Get photo by id.
     *
     * @param int $id Photo id.
     *
     * @return Photo
     */
    public function getPhoto(int $id): Photo;

    /**
     * Get URL to a photo.
     *
     * @param string $storageUid Photo storage uid.
     *
     * @return string
     */
    public function getPhotoUrl(string $storageUid): string;

    /**
     * Allows to retrieve photo's contents by id.
     *
     * @param int $photoId Photo id.
     *
     * @return string
     */
    public function getPhotoContents(int $photoId): string;

    /**
     * Allows to retrieve photo's contents as streamed response (for download).
     *
     * @param int $photoId Photo id.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPhotoContentsAsResponse(int $photoId): Response;

    /**
     * Allows to download multiple photos as ZIP attachment.
     *
     * @param array $photoIds Photo identifiers.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPhotosZipAsResponse(array $photoIds): Response;

    /**
     * Creates a photo from local file path and saves to the database and storage.
     *
     * @param string $filePath   Path to the local image file.
     * @param int    $originalId Optional id of the parent photo (for thumbnails).
     *
     * @return \App\Components\Photos\Models\Photo Saved photo.
     */
    public function createPhotoFromPath(string $filePath, int $originalId = null): Photo;

    /**
     * Creates a photo from uploaded file and saves to the database and storage.
     *
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\File $file File to be saved.
     *
     * @return \App\Components\Photos\Models\Photo Saved photo.
     */
    public function createPhotoFromFile(UploadedFile $file): Photo;

    /**
     * Allows to edit existing photo from uploaded file (re-upload).
     *
     * @param int                                                 $photoId Photo id.
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\File $file    File to be saved.
     *
     * @return \App\Components\Photos\Models\Photo
     */
    public function updatePhotoFromFile(int $photoId, UploadedFile $file): Photo;

    /**
     * Allows to delete a photo by id.
     *
     * @param int  $photoId Photo id.
     * @param bool $force   Force thumbnail deletion.
     */
    public function deletePhoto(int $photoId, bool $force = false): void;

    /**
     * Allows to generate a thumbnail for the photo.
     *
     * @param int $photoId Photo id.
     * @param int $width   Thumbnail width.
     * @param int $height  Thumbnail height.
     */
    public function generateThumbnail(int $photoId, int $width, int $height): void;
}
