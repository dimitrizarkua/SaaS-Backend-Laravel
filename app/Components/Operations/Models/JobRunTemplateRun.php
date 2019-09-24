<?php

namespace App\Components\Operations\Models;

use App\Models\ApiRequestFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OpenApi\Annotations as OA;

/**
 * Class JobRunTemplateRun
 *
 * @mixin \Eloquent
 *
 * @property int                                                   $id
 * @property int                                                   $job_run_template_id
 * @property string|null                                           $name
 * @property \Illuminate\Support\Carbon                            $created_at
 * @property \Illuminate\Support\Carbon                            $updated_at
 *
 * @property-read \App\Components\Operations\Models\JobRunTemplate $template
 * @property-read \Illuminate\Support\Collection|User[]            $assignedUsers
 * @property-read \Illuminate\Support\Collection|Vehicle[]         $assignedVehicles
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","job_run_template_id","created_at","updated_at"}
 * )
 */
class JobRunTemplateRun extends Model
{
    use ApiRequestFillable;

    public $timestamps = true;

    /**
     * @OA\Property(property="id", type="integer", description="Job run identifier", example=1)
     * @OA\Property(property="job_run_template_id", type="integer", description="Template identifier", example=1)
     * @OA\Property(property="name", type="string", description="Name", example="Run 1")
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Parent template.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(JobRunTemplate::class, 'job_run_template_id');
    }

    /**
     * Assigned users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'job_run_template_run_crew_assignments',
            'job_run_template_run_id',
            'crew_user_id'
        );
    }

    /**
     * Assigned vehicles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedVehicles(): BelongsToMany
    {
        return $this->belongsToMany(
            Vehicle::class,
            'job_run_template_run_vehicle_assignments',
            'job_run_template_run_id',
            'vehicle_id'
        );
    }
}
