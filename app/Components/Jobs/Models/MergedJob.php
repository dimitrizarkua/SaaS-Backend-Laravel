<?php

namespace App\Components\Jobs\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class MergedJob
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                  $source_job_id
 * @property int                                  $destination_job_id
 * @property-read \App\Components\Jobs\Models\Job $mergedTo
 * @property-read \App\Components\Jobs\Models\Job $mergedFrom
 */
class MergedJob extends Model
{
    use HasCompositePrimaryKey;

    protected $table = 'merged_jobs';

    public $incrementing = false;
    public $timestamps   = false;

    protected $fillable   = ['source_job_id', 'destination_job_id'];
    protected $primaryKey = ['source_job_id', 'destination_job_id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mergedTo(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'destination_job_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mergedFrom(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'source_job_id');
    }
}
