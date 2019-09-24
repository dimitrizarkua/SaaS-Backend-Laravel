<?php

namespace App\Jobs\Photos;

use App\Components\Photos\Exceptions\NotAllowedException;
use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Utils\FileIO;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Constraint;
use Intervention\Image\Facades\Image;

/**
 * Class CreatePhotoThumbnail
 *
 * @package App\Jobs\Photos
 */
class CreatePhotoThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $photoId;

    /**
     * @var int
     */
    protected $thumbWidth;

    /**
     * @var int
     */
    protected $thumbHeight;

    /**
     * CreateThumbnail constructor.
     *
     * @param int $photoId
     * @param int $thumbWidth
     * @param int $thumbHeight
     */
    public function __construct(int $photoId, int $thumbWidth, int $thumbHeight)
    {
        $this->photoId     = $photoId;
        $this->thumbWidth  = $thumbWidth;
        $this->thumbHeight = $thumbHeight;
    }

    /**
     * Execute the job.
     *
     * @param \App\Components\Photos\Interfaces\PhotosServiceInterface $photosService
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle(PhotosServiceInterface $photosService)
    {
        $content = $photosService->getPhotoContents($this->photoId);
        $image   = Image::make($content);

        if ($this->thumbWidth >= $image->width() && $this->thumbHeight >= $image->height()) {
            return;
        }

        $constraintCallback = function (Constraint $constraint) {
            $constraint->aspectRatio();
        };

        if ($image->width() > $image->height()) {
            $image->resize($this->thumbWidth, null, $constraintCallback);
        } else {
            $image->resize(null, $this->thumbHeight, $constraintCallback);
        }

        $tmpFile = FileIO::getTmpFilePath();
        $image->save($tmpFile);

        try {
            $photosService->createPhotoFromPath($tmpFile, $this->photoId);

            Log::info(
                sprintf(
                    'New thumbnail [THUMB_SIZE:%dx%d] has has been created for photo [PHOTO_ID:%d].',
                    $this->thumbWidth,
                    $this->thumbHeight,
                    $this->photoId
                ),
                [
                    'photo_id'     => $this->photoId,
                    'thumb_width'  => $this->thumbWidth,
                    'thumb_height' => $this->thumbHeight,
                ]
            );
        } catch (NotAllowedException $exception) {
            Log::info(
                sprintf(
                    'Thumbnail [THUMB_SIZE:%dx%d] for photo [PHOTO_ID:%d] already exists.',
                    $this->thumbWidth,
                    $this->thumbHeight,
                    $this->photoId
                ),
                [
                    'photo_id'     => $this->photoId,
                    'thumb_width'  => $this->thumbWidth,
                    'thumb_height' => $this->thumbHeight,
                ]
            );
        } finally {
            File::delete($tmpFile);
        }
    }
}
