<?php

namespace App\Components\Jobs\Models;

use App\Components\Messages\Models\Message;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class JobMessage
 *
 * @package App\Components\Jobs\Models
 *
 * @mixin \Eloquent
 * @property int                                          $job_id
 * @property int                                          $message_id
 * @property int|null                                     $job_status_id
 * @property Carbon                                       $created_at
 * @property Carbon|null                                  $read_at
 * @property Carbon|null                                  $deleted_at
 * @property-read \App\Components\Jobs\Models\Job         $job
 * @property-read \App\Components\Messages\Models\Message $message
 */
class JobMessage extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'job_messages';
    protected $fillable   = ['job_id', 'message_id', 'read_at'];
    protected $primaryKey = ['job_id', 'message_id'];

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
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
