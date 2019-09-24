<?php

namespace App\Components\Jobs\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobFollower
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                  $job_id
 * @property int                                  $user_id
 * @property-read \App\Components\Jobs\Models\Job $job
 * @property-read \App\Models\User                $user
 */
class JobFollower extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'job_followers';
    protected $fillable   = ['job_id', 'user_id'];
    protected $primaryKey = ['job_id', 'user_id'];

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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
