<?php

namespace App\Components\Jobs\Models;

use App\Components\Documents\Models\Document;
use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class JobDocument
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                            $job_id
 * @property int                                            $document_id
 * @property int|null                                       $creator_id
 * @property string                                         $type
 * @property string|null                                    $description
 * @property Carbon                                         $created_at
 * @property Carbon                                         $updated_at
 *
 * @property-read \App\Components\Jobs\Models\Job           $job
 * @property-read \App\Components\Documents\Models\Document $document
 * @property-read \App\Models\User                          $creator
 */
class JobDocument extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'job_documents';
    protected $guarded    = ['created_at', 'updated_at'];
    protected $primaryKey = ['job_id', 'document_id'];

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
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
