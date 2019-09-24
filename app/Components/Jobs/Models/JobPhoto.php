<?php

namespace App\Components\Jobs\Models;

use App\Components\Photos\Models\Photo;
use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class JobPhoto
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 *
 * @property int                                      $job_id
 * @property int                                      $photo_id
 * @property int|null                                 $creator_id
 * @property int|null                                 $modified_by_id
 * @property string|null                              $description
 * @property Carbon                                   $created_at
 * @property Carbon                                   $updated_at
 *
 * @property-read \App\Components\Jobs\Models\Job     $job
 * @property-read \App\Components\Photos\Models\Photo $photo
 * @property-read \App\Models\User                    $creator
 * @property-read \App\Models\User                    $modifiedBy
 */
class JobPhoto extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = true;

    protected $table      = 'job_photo';
    protected $guarded    = ['created_at', 'updated_at'];
    protected $primaryKey = ['job_id', 'photo_id'];

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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by_id');
    }
}
