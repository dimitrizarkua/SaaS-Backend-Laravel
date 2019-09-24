<?php

namespace App\Components\Jobs\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskStatus
 *
 * @mixin \Eloquent
 *
 * @property int                                      $id
 * @property int                                      $job_task_id
 * @property int|null                                 $user_id
 * @property string                                   $status
 * @property string|null                              $reason
 * @property \Illuminate\Support\Carbon               $created_at
 *
 * @property-read \App\Components\Jobs\Models\JobTask $task
 * @property-read \App\Models\User                    $user
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","job_task_id","status","created_at"}
 * )
 */
class JobTaskStatus extends Model
{
    const UPDATED_AT = null;

    public $timestamps = true;

    /**
     * @OA\Property(property="id", type="integer", description="Job task status identifier", example=1)
     * @OA\Property(property="job_task_id", type="integer", description="Task identifier", example=1)
     * @OA\Property(property="user_id", type="integer", description="User identifier", example=1)
     * @OA\Property(
     *     property="status",
     *     description="Task status",
     *     allOf={@OA\Schema(ref="#/components/schemas/JobTaskStatuses")}
     * )
     * @OA\Property(
     *     property="reason",
     *     type="string",
     *     nullable=true,
     *     description="Reason of the status change",
     *     example="Some text"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * Parent task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(JobTask::class, 'job_task_id');
    }

    /**
     * Creator user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
