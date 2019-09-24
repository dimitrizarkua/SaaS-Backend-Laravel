<?php

namespace App\Components\Jobs\Models;

use App\Components\Tags\Models\Tag;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobTag
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                  $job_id
 * @property int                                  $tag_id
 * @property-read \App\Components\Jobs\Models\Job $job
 * @property-read \App\Components\Tags\Models\Tag $tag
 */
class JobTag extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'job_tag';
    protected $fillable   = ['job_id', 'tag_id'];
    protected $primaryKey = ['job_id', 'tag_id'];

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
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
