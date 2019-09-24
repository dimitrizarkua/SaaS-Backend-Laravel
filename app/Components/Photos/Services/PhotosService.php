<?php

namespace App\Components\Photos\Services;

use App\Components\Photos\Exceptions\NotAllowedException;
use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Components\Photos\Models\Photo;
use App\Jobs\Photos\CreatePhotoThumbnail;
use App\Utils\FileIO;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PhotosService
 *
 * @package App\Components\Photos\Services
 */
class PhotosService implements PhotosServiceInterface
{
    /** @var string */
    private $diskName = null;

    /**
     * {@inheritdoc}
     */
    public function getDiskName(): string
    {
        $diskName = $this->diskName ?? env('PHOTOS_STORAGE_DISK');

        if (null === $diskName) {
            $diskName = App::environment(['local', 'testing']) ? 'photos_local' : 'photos_s3';
        }

        return $diskName;
    }

    /**
     * {@inheritdoc}
     */
    public function setDiskName(string $diskName): void
    {
        $this->diskName = $diskName;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getPhoto(int $photoId): Photo
    {
        return Photo::findOrFail($photoId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPhotoUrl(string $storageUid): string
    {
        $url = rtrim(env('PHOTOS_DISTRIBUTION_BASE_URL'), '/') . '/' . $storageUid;

        return $this->getDisk()->getAdapter() instanceof AwsS3Adapter
            ? $this->getSignedUrl($url)
            : $url;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getPhotoContents(int $photoId): string
    {
        $photo = $this->getPhoto($photoId);

        return $this->getDisk()->get($photo->storage_uid);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getPhotoContentsAsResponse(int $photoId): Response
    {
        $photo = $this->getPhoto($photoId);

        return $this->getDisk()->response($photo->storage_uid, Str::ascii($photo->file_name));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getPhotosZipAsResponse(array $photoIds): Response
    {
        $photoIds = array_unique($photoIds);

        $zipName = 'Photos_' . (string)Str::uuid() . '.zip';
        $zipFile = FileIO::getTmpFilePath($zipName);

        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE);

        try {
            foreach ($photoIds as $photoId) {
                $photo = $this->getPhoto($photoId);
                $zip->addFromString($photo->file_name, $this->getPhotoContents($photoId));
            }
        } catch (\Exception $exception) {
            $zip->close();
            File::delete($zipFile);

            throw $exception;
        }

        $zip->close();

        return response()->download(
            $zipFile,
            $zipName,
            [
                'Content-Type: application/zip',
                'Content-Length: ' . filesize($zipFile),
                'Content-Disposition: attachment; filename="' . $zipName . '"',
            ]
        )
            ->deleteFileAfterSend(true);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createPhotoFromPath(string $filePath, int $originalId = null): Photo
    {
        $storageUid = $this->store(File::get($filePath));
        if ($originalId && Photo::containsStorageUid($storageUid, $originalId)) {
            throw new NotAllowedException('Thumbnail already exists.');
        }

        $photo = new Photo();

        $photo->guard(['*' => false]);
        $image    = Image::make($filePath);
        $fileName = File::name($filePath);

        $photo->fill([
            'storage_uid'       => $storageUid,
            'file_name'         => $originalId ? 'thumbnail_' . $fileName : $fileName,
            'original_photo_id' => $originalId,
            'file_size'         => File::size($filePath),
            'file_hash'         => $storageUid,
            'mime_type'         => $image->mime(),
            'width'             => $image->width(),
            'height'            => $image->height(),
        ]);

        $photo->saveOrFail();

        return $photo;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createPhotoFromFile(UploadedFile $file): Photo
    {
        $storageUid = $this->storeFile($file);

        $photo = new Photo();

        $photo->guard(['*' => false]);
        $image = Image::make($file->getRealPath());
        $photo->fill([
            'storage_uid'       => $storageUid,
            'file_name'         => $file->getClientOriginalName(),
            'original_photo_id' => null,
            'file_size'         => $file->getSize(),
            'file_hash'         => $storageUid,
            'mime_type'         => $file->getMimeType(),
            'width'             => $image->width(),
            'height'            => $image->height(),
        ]);

        $photo->saveOrFail();

        return $photo;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function updatePhotoFromFile(int $photoId, UploadedFile $file): Photo
    {
        $photo = $this->getPhoto($photoId);
        if ($photo->original_photo_id) {
            throw new NotAllowedException('Could not update a thumbnail.');
        }

        $newUid = $this->storeFile($file);
        $oldUid = $photo->storage_uid;

        if ($newUid === $oldUid) {
            $photo->file_name = $file->getClientOriginalName();
            $photo->saveOrFail();

            return $photo;
        }

        $image = Image::make($file->getRealPath());

        DB::transaction(function () use ($photo, $file, $image, $newUid, $oldUid) {
            $photo->guard(['*' => false]);
            $photo->update([
                'storage_uid'       => $newUid,
                'file_name'         => $file->getClientOriginalName(),
                'original_photo_id' => null,
                'file_size'         => $file->getSize(),
                'file_hash'         => $newUid,
                'mime_type'         => $file->getMimeType(),
                'width'             => $image->width(),
                'height'            => $image->height(),
            ]);

            foreach ($photo->thumbnails as $thumbnail) {
                $this->generateThumbnail($photo->id, $thumbnail->width, $thumbnail->height);

                $thumbnail->delete();
            }
        });

        if (!Photo::containsStorageUid($oldUid)) {
            $this->getDisk()->delete($oldUid);
        }

        return $photo;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function deletePhoto(int $photoId, bool $force = false): void
    {
        $photo = $this->getPhoto($photoId);
        if ($photo->jobs()->exists()) {
            throw new NotAllowedException('Could not delete a photo attached to a job.');
        }
        if ($photo->original_photo_id && !$force) {
            throw new NotAllowedException('Could not delete a thumbnail.');
        }

        $photoStorageId = $photo->storage_uid;
        $thumbnailStorageIds = $photo->thumbnails()->pluck('storage_uid')->toArray();

        try {
            $photo->delete();
        } catch (\Exception $e) {
            throw new NotAllowedException('Could not delete a photo attached to some other entity.');
        }
        if (!Photo::containsStorageUid($photoStorageId)) {
            $this->getDisk()->delete($photoStorageId);
        }
        if ($thumbnailStorageIds) {
            $this->getDisk()->delete($thumbnailStorageIds);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateThumbnail(int $photoId, int $width, int $height): void
    {
        CreatePhotoThumbnail::dispatch($photoId, $width, $height)->onQueue('photos');
    }

    /**
     * @param string      $content
     * @param string|null $storageUid
     *
     * @return string Storage id.
     */
    private function store(string $content, string $storageUid = null): string
    {
        if (!$storageUid) {
            $storageUid = hash('sha256', $content);
        }
        if (!$this->getDisk()->has($storageUid)) {
            $this->getDisk()->put($storageUid, $content);
        }

        return $storageUid;
    }

    /**
     * @param \Illuminate\Http\UploadedFile $file
     * @param string                        $storageUid
     *
     * @return string Storage id.
     */
    private function storeFile(UploadedFile $file, string $storageUid = null): string
    {
        if (!$storageUid) {
            $storageUid = hash_file('sha256', $file->getRealPath());
        }
        if (!$this->getDisk()->has($storageUid)) {
            $file->storeAs('/', $storageUid, ['disk' => $this->getDiskName()]);
        }

        return $storageUid;
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     */
    private function getDisk(): FilesystemAdapter
    {
        return Storage::disk($this->getDiskName());
    }

    /**
     * @param string $url
     *
     * @return string
     */
    private function getSignedUrl(string $url): string
    {
        $keyPairId      = env('PHOTOS_SIGNATURE_KEY_PAIR_ID');
        $privateKeyPath = env('PHOTOS_SIGNATURE_PRIVATE_KEY_PATH');

        if ($keyPairId && $privateKeyPath) {
            $cloudFront = new \Aws\CloudFront\CloudFrontClient([
                'region'  => env('AWS_REGION'),
                'version' => '2018-06-18',
            ]);

            $url = $cloudFront->getSignedUrl([
                'url'         => $url,
                'expires'     => strtotime('+1 day'),
                'private_key' => $privateKeyPath,
                'key_pair_id' => $keyPairId,
            ]);
        }

        return $url;
    }
}
