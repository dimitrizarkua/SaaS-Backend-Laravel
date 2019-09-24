<?php

namespace App\Components\Jobs\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class JobStatus
 *
 * @property int                        $id
 * @property int                        $job_id
 * @property int|null                   $user_id
 * @property string                     $status
 * @property string|null                $note
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @property-read Job                   $job
 * @property-read User|null             $user
 *
 * @method static Builder|JobStatus whereCreatedAt($value)
 * @method static Builder|JobStatus whereId($value)
 * @method static Builder|JobStatus whereJobId($value)
 * @method static Builder|JobStatus whereNote($value)
 * @method static Builder|JobStatus whereStatus($value)
 * @method static Builder|JobStatus whereUserId($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id", "job_id", "status", "created_at"}
 * )
 */
class JobStatus extends Model
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="Job Status Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="job_id",
     *     description="Job Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="user_id",
     *     description="User Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="status",
     *     ref="#/components/schemas/JobStatuses"
     * ),
     * @OA\Property(
     *     property="note",
     *     description="Note to Job Status",
     *     type="string",
     *     nullable=true,
     *     example="Job is active",
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     */

    const UPDATED_AT = null;

    public $timestamps = true;

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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

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
     * Associated job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * A user who put a job in this state (or changed the status to this if you like).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
