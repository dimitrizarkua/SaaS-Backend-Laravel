<?php

namespace App\Components\Photos\Models;

use App\Components\Jobs\Models\Job;
use App\Components\Photos\Interfaces\PhotosServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Photo
 *
 * @package App\Components\Photos\Models
 *
 * @mixin \Eloquent
 * @property int                        $id
 * @property string                     $storage_uid
 * @property string                     $file_name
 * @property integer                    $file_size
 * @property string                     $file_hash
 * @property string|null                $mime_type
 * @property int                        $width
 * @property int                        $height
 * @property int|null                   $original_photo_id
 * @property string                     $url
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read Photo|null            $original
 * @property-read Collection|Photo[]    $thumbnails
 * @property-read Collection|Job[]      $jobs
 *
 * @OA\Schema(
 *     type="object",
 *     nullable=true,
 *     required={"id","file_name","file_size","width","height","created_at","updated_at"}
 * )
 */
class Photo extends Model
{
    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(
     *     property="file_name",
     *     description="File name (including extension)",
     *     type="string",
     *     example="photo.jpg"
     * )
     * @OA\Property(
     *     property="file_size",
     *     description="File size in bytes",
     *     type="integer",
     *     example=573187
     * )
     * @OA\Property(
     *     property="mime_type",
     *     description="Mime type",
     *     type="string",
     *     example="image/jpeg"
     * )
     * @OA\Property(
     *     property="width",
     *     description="Image width",
     *     type="integer",
     *     example="1024"
     * )
     * @OA\Property(
     *     property="height",
     *     description="Image height",
     *     type="integer",
     *     example="768"
     * )
     * @OA\Property(
     *     property="original_photo_id",
     *     description="Original photo id",
     *     type="integer",
     *     example=1
     * )
     * @OA\Property(
     *     property="url",
     *     description="Photo's direct URL",
     *     type="string",
     *     example="http://url-to-photo"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     */

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'storage_uid',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'storage_uid',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['url'];

    /**
     * Define relationship between thumbnail and original photo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function original()
    {
        return $this->belongsTo(Photo::class, 'original_photo_id');
    }

    /**
     * Define relationship between original image and thumbnails.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function thumbnails(): HasMany
    {
        return $this->hasMany(Photo::class, 'original_photo_id');
    }

    /**
     * Returns jobs which this photo belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class)->withPivot(['creator_id', 'description']);
    }

    /**
     * @return string
     */
    public function getUrlAttribute(): string
    {
        return app()->make(PhotosServiceInterface::class)->getPhotoUrl($this->storage_uid);
    }

    /**
     * @param string $storageUid
     * @param int    $originalId
     *
     * @return bool
     */
    public static function containsStorageUid(string $storageUid, int $originalId = null): bool
    {
        $query = self::query()->where('storage_uid', '=', $storageUid);
        if ($originalId) {
            $query->where('original_photo_id', '=', $originalId);
        }

        return $query->exists();
    }
}
