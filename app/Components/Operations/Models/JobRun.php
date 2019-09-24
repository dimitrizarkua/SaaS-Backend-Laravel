<?php

namespace App\Components\Operations\Models;

use App\Components\Jobs\Models\JobTask;
use App\Components\Locations\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class JobRun
 *
 * @mixin \Eloquent
 *
 * @property int                                            $id
 * @property int                                            $location_id
 * @property string|null                                    $name
 * @property \Illuminate\Support\Carbon                     $date
 *
 * @property-read \App\Components\Locations\Models\Location $location
 * @property-read \Illuminate\Support\Collection|User[]     $assignedUsers
 * @property-read \Illuminate\Support\Collection|Vehicle[]  $assignedVehicles
 * @property-read \Illuminate\Support\Collection|JobTask[]  $assignedTasks
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","location_id","date"}
 * )
 */
class JobRun extends Model
{
    public $timestamps = false;

    /**
     * @OA\Property(property="id", type="integer", description="Job run identifier", example=1)
     * @OA\Property(property="location_id", type="integer", description="Location identifier", example=1)
     * @OA\Property(property="name", type="string", description="Name", example="Run 1")
     * @OA\Property(
     *     property="date",
     *     description="Run date",
     *     type="string",
     *     format="date",
     *     example="2018-11-10"
     * )
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Location that the job run belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
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
            'job_run_crew_assignments',
            'job_run_id',
            'crew_user_id'
        )->withPivot('assigner_id', 'created_at');
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
            'job_run_vehicle_assignments',
            'job_run_id',
            'vehicle_id'
        )->withPivot('driver_name', 'assigner_id', 'created_at');
    }

    /**
     * Assigned tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(JobTask::class, 'job_run_id');
    }

    /**
     * Check if the run has conflicting tasks within the specified time range.
     *
     * @param int                        $taskId   Task id.
     * @param \Illuminate\Support\Carbon $startsAt Starting time.
     * @param \Illuminate\Support\Carbon $endsAt   Ending time.
     *
     * @return boolean
     */
    public function hasConflictingTasks(int $taskId, Carbon $startsAt, Carbon $endsAt): bool
    {
        return JobTask::queryOverlaps($startsAt, $endsAt)
            ->select('job_tasks.id')
            ->where('job_tasks.job_run_id', '=', $this->id)
            ->where('job_tasks.id', '!=', $taskId)
            ->exists();
    }
}
