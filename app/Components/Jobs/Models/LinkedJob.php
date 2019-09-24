<?php

namespace App\Components\Jobs\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class LinkedJob
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                  $job_id
 * @property int                                  $linked_job_id
 * @property-read \App\Components\Jobs\Models\Job $linkedTo
 * @property-read \App\Components\Jobs\Models\Job $linkedFrom
 */
class LinkedJob extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'job_links';

    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable   = ['job_id', 'linked_job_id'];
    protected $primaryKey = ['job_id', 'linked_job_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function linkedTo(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function linkedFrom(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'linked_job_id');
    }
}
