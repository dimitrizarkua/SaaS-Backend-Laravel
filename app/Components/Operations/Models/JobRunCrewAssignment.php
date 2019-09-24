<?php

namespace App\Components\Operations\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class JobRunCrewAssignment
 *
 * @mixin \Eloquent
 *
 * @property int                                           $job_run_id
 * @property int                                           $crew_user_id
 * @property int|null                                      $assigner_id
 * @property \Illuminate\Support\Carbon                    $created_at
 *
 * @property-read \App\Components\Operations\Models\JobRun $run
 * @property-read \App\Models\User                         $user
 * @property-read \App\Models\User                         $assigner
 *
 * @OA\Schema(
 *     type="object",
 *     required={"job_run_id","crew_user_id","created_at"}
 * )
 */
class JobRunCrewAssignment extends Model
{
    use HasCompositePrimaryKey;

    const UPDATED_AT = null;

    protected $primaryKey = ['job_run_id', 'crew_user_id'];

    public $incrementing = false;
    public $timestamps   = true;

    /**
     * @OA\Property(property="job_run_id", type="integer", description="Job run identifier", example=1)
     * @OA\Property(property="crew_user_id", type="integer", description="Assigned user identifier", example=1)
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
     * Parent run.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function run(): BelongsTo
    {
        return $this->belongsTo(JobRun::class, 'job_run_id');
    }

    /**
     * Assigned user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'crew_user_id');
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
