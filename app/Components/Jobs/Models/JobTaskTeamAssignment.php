<?php

namespace App\Components\Jobs\Models;

use App\Components\Teams\Models\Team;
use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskTeamAssignment
 *
 * @mixin \Eloquent
 *
 * @property int                                      $job_task_id
 * @property int                                      $team_id
 * @property int|null                                 $assigner_id
 * @property \Illuminate\Support\Carbon               $created_at
 *
 * @property-read \App\Components\Jobs\Models\JobTask $task
 * @property-read \App\Components\Teams\Models\Team   $team
 * @property-read \App\Models\User                    $assigner
 *
 * @OA\Schema(
 *     type="object",
 *     required={"job_task_id","team_id","created_at"}
 * )
 */
class JobTaskTeamAssignment extends Model
{
    use HasCompositePrimaryKey;

    const UPDATED_AT = null;

    protected $primaryKey = ['job_task_id', 'team_id'];

    public $incrementing = false;
    public $timestamps   = true;

    /**
     * @OA\Property(property="job_task_id", type="integer", description="Job task identifier", example=1)
     * @OA\Property(property="team_id", type="integer", description="Team identifier", example=1)
     * @OA\Property(property="assigner_id", type="integer", description="Assigner user identifier", example=1)
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
     * Assigned team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * Assigner user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
}
