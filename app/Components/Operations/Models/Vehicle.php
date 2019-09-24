<?php

namespace App\Components\Operations\Models;

use App\Components\Jobs\Models\JobTask;
use App\Components\Locations\Models\Location;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class Vehicle
 *
 * @mixin \Eloquent
 *
 * @property int                                                  $id
 * @property int                                                  $location_id
 * @property string                                               $type
 * @property string                                               $make
 * @property string                                               $model
 * @property string                                               $registration
 * @property \Illuminate\Support\Carbon|null                      $rent_starts_at
 * @property \Illuminate\Support\Carbon|null                      $rent_ends_at
 * @property \Illuminate\Support\Carbon                           $created_at
 * @property \Illuminate\Support\Carbon                           $updated_at
 * @property \Illuminate\Support\Carbon|null                      $deleted_at
 *
 * @property-read \App\Components\Locations\Models\Location       $location
 * @property-read \Illuminate\Support\Collection|VehicleStatus[]  $statuses
 * @property-read \App\Components\Operations\Models\VehicleStatus $latestStatus
 * @property-read \Illuminate\Support\Collection|JobTask[]        $assignedTasks
 * @property-read \Illuminate\Support\Collection|JobRun[]         $assignedRuns
 * @property bool|null                                            $is_booked
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","location_id","type","make","model","registration","created_at","updated_at"}
 * )
 */
class Vehicle extends Model
{
    use ApiRequestFillable, SoftDeletes, DateTimeFillable;

    /**
     * @OA\Property(property="id", type="integer", description="Vehicle identifier", example=1)
     * @OA\Property(property="location_id", type="integer", description="Location identifier", example=1)
     * @OA\Property(property="type", type="string", description="Vehicle type", example="Cargo van")
     * @OA\Property(property="make", type="string", description="Vehicle make", example="Ford")
     * @OA\Property(property="model", type="string", description="Vehicle model", example="Transit")
     * @OA\Property(property="registration", type="string", description="Registration", example="S550 ABC")
     * @OA\Property(
     *     property="rent_starts_at",
     *     type="string",
     *     format="date-time",
     *     nullable=true,
     *     description="Rent start time",
     *     example="2018-10-10T09:10:11Z"
     * )
     * @OA\Property(
     *     property="rent_ends_at",
     *     type="string",
     *     format="date-time",
     *     nullable=true,
     *     description="Rent end time",
     *     example="2018-11-10T09:10:11Z"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * @OA\Property(property="deleted_at", type="string", format="date-time")
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'rent_starts_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'rent_ends_at'   => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'rent_starts_at',
        'rent_ends_at',
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
        'deleted_at',
    ];

    /**
     * Location that the vehicle belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Latest (or current) vehicle status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this
            ->hasOne(VehicleStatus::class, 'vehicle_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Vehicle statuses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this
            ->hasMany(VehicleStatus::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Assigned job tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            JobTask::class,
            'job_task_vehicle_assignments',
            'vehicle_id',
            'job_task_id'
        )->withPivot('assigner_id', 'created_at');
    }

    /**
     * Assigned job runs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedRuns(): BelongsToMany
    {
        return $this->belongsToMany(
            JobRun::class,
            'job_run_vehicle_assignments',
            'vehicle_id',
            'job_run_id'
        )->withPivot('assigner_id', 'created_at');
    }

    /**
     * Allows to change status of this vehicle.
     *
     * @param VehicleStatusType $statusType
     * @param int               $userId Optional id of user who is changing status.
     *
     * @return \App\Components\Operations\Models\VehicleStatus
     */
    public function changeStatus(VehicleStatusType $statusType, int $userId): VehicleStatus
    {
        /** @var \App\Components\Operations\Models\VehicleStatus $status */
        $status = $this->statuses()->create([
            'vehicle_status_type_id' => $statusType->id,
            'user_id'                => $userId,
        ]);

        return $status;
    }

    /**
     * Check if the vehicle has runs on the specified date.
     *
     * @param \Illuminate\Support\Carbon $date
     *
     * @return boolean
     */
    public function hasRunsOnDate(Carbon $date): bool
    {
        return $this->assignedRuns()->where('date', '=', $date)->exists();
    }

    /**
     * Setter for rent_starts_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setRentStartsAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('rent_starts_at', $datetime);
    }

    /**
     * Setter for rent_ends_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setRentEndsAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('rent_ends_at', $datetime);
    }
}
