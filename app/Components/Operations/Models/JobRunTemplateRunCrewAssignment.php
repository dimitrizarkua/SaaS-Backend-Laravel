<?php

namespace App\Components\Operations\Models;

use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class JobRunTemplateRunCrewAssignment
 *
 * @mixin \Eloquent
 *
 * @property int                                                      $job_run_template_run_id
 * @property int                                                      $crew_user_id
 *
 * @property-read \App\Components\Operations\Models\JobRunTemplateRun $templateRun
 * @property-read \App\Models\User                                    $user
 *
 * @OA\Schema(
 *     type="object",
 *     required={"job_run_template_run_id","crew_user_id"}
 * )
 */
class JobRunTemplateRunCrewAssignment extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['job_run_template_run_id', 'crew_user_id'];

    public $incrementing = false;
    public $timestamps   = false;

    /**
     * @OA\Property(property="job_run_template_run_id", type="integer", description="Run identifier", example=1)
     * @OA\Property(property="crew_user_id", type="integer", description="Assigned user identifier", example=1)
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_run_template_run_id',
        'crew_user_id',
    ];

    /**
     * Parent template run.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function templateRun(): BelongsTo
    {
        return $this->belongsTo(JobRunTemplateRun::class, 'job_run_template_run_id');
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
}
