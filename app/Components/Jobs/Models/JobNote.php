<?php

namespace App\Components\Jobs\Models;

use App\Components\Notes\Models\Note;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class JobNote
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                    $job_id
 * @property int                                    $note_id
 * @property int|null                               $job_status_id
 * @property Carbon                                 $created_at
 * @property Carbon|null                            $deleted_at
 * @property boolean                                $mergeable
 *
 * @property-read \App\Components\Jobs\Models\Job   $job
 * @property-read \App\Components\Notes\Models\Note $note
 */
class JobNote extends Model
{
    use HasCompositePrimaryKey, SoftDeletes;

    const UPDATED_AT = null;

    public $incrementing = false;
    public $timestamps   = true;

    protected $table      = 'job_notes';
    protected $fillable   = ['job_id', 'note_id', 'job_status_id'];
    protected $primaryKey = ['job_id', 'note_id'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'mergeable'  => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'deleted_at',
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
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
