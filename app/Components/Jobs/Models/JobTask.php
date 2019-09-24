<?php

namespace App\Components\Jobs\Models;

use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\JobTasksIndexConfigurator;
use App\Components\Jobs\JobTasksSearchRules;
use App\Components\Operations\Models\JobRun;
use App\Components\Operations\Models\Vehicle;
use App\Components\Teams\Models\Team;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use App\Models\HasProtectedFields;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class JobTask
 *
 * @mixin \Eloquent
 *
 * @property int                                                                 $id
 * @property int                                                                 $job_id
 * @property int                                                                 $job_task_type_id
 * @property int|null                                                            $job_run_id
 * @property string|null                                                         $name
 * @property string|null                                                         $internal_note
 * @property string|null                                                         $scheduling_note
 * @property string|null                                                         $kpi_missed_reason
 * @property \Illuminate\Support\Carbon|null                                     $due_at
 * @property \Illuminate\Support\Carbon|null                                     $starts_at
 * @property \Illuminate\Support\Carbon|null                                     $ends_at
 * @property \Illuminate\Support\Carbon|null                                     $snoozed_until
 * @property \Illuminate\Support\Carbon                                          $created_at
 * @property \Illuminate\Support\Carbon|null                                     $kpi_missed_at
 *
 * @property-read \App\Components\Jobs\Models\Job                                $job
 * @property-read \App\Components\Jobs\Models\JobTaskType                        $type
 * @property-read \App\Components\Operations\Models\JobRun                       $run
 * @property-read \Illuminate\Support\Collection|User[]                          $assignedUsers
 * @property-read \Illuminate\Support\Collection|Vehicle[]                       $assignedVehicles
 * @property-read \Illuminate\Support\Collection|Team[]                          $assignedTeams
 * @property-read \Illuminate\Support\Collection|JobTaskStatus[]                 $statuses
 * @property-read \Illuminate\Support\Collection|JobTaskScheduledPortionStatus[] $scheduledStatuses
 * @property-read \App\Components\Jobs\Models\JobTaskStatus                      $latestStatus
 * @property-read \App\Components\Jobs\Models\JobTaskScheduledPortionStatus      $latestScheduledStatus
 *
 * @method static Builder scheduledForWeek(Carbon $date)
 * @method static Builder shouldBeUnsnoozed()
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","job_id","job_task_type_id"}
 * )
 */
class JobTask extends Model
{
    use ApiRequestFillable, DateTimeFillable, Searchable, HasProtectedFields;

    public const UPDATED_AT = null;

    protected $protectedFields = ['kpi_missed_at'];

    /**
     * @OA\Property(property="id", type="integer", description="Job task identifier", example=1)
     * @OA\Property(property="job_id", type="integer", description="Job identifier", example=1)
     * @OA\Property(property="job_task_type_id", type="integer", description="Job task type identifier", example=1)
     * @OA\Property(property="job_run_id", type="integer", nullable=true, description="Job run identifier", example=1)
     * @OA\Property(property="name", type="string", nullable=true, description="Name", example="Customer call")
     * @OA\Property(
     *     property="internal_note",
     *     type="string",
     *     nullable=true,
     *     description="Internal note",
     *     example="Some text"
     * )
     * @OA\Property(
     *     property="scheduling_note",
     *     type="string",
     *     nullable=true,
     *     description="Scheduling note",
     *     example="Some text",
     * )
     * @OA\Property(
     *     property="kpi_missed_reason",
     *     type="string",
     *     nullable=true,
     *     description="Reason of why KPI missed",
     *     example="Some text"
     * )
     * @OA\Property(property="due_at", type="string", description="Due at time", format="date-time")
     * @OA\Property(
     *     property="starts_at",
     *     type="string",
     *     nullable=true,
     *     description="Starts at time",
     *     format="date-time"
     * )
     * @OA\Property(
     *     property="ends_at",
     *     type="string",
     *     nullable=true,
     *     description="Ends at time",
     *     format="date-time"
     *
     * )
     * @OA\Property(
     *     property="snoozed_until",
     *     description="Time until which job task is snoozed",
     *     type="string",
     *     nullable=true,
     *     format="date-time"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(
     *     property="kpi_missed_at",
     *     description="Date time when KPI will be missed. Null means that for the task there is no any KPI",
     *     type="string",
     *     format="date-time",
     *     nullable=true
     * )
     */

    protected $indexConfigurator = JobTasksIndexConfigurator::class;

    protected $searchRules = [
        JobTasksSearchRules::class,
    ];

    /**
     * Elasticsearch mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'job_id'      => [
                'type' => 'text',
            ],
            'job_run_id'  => [
                'type' => 'long',
            ],
            'name'        => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
                'fielddata'       => true,
            ],
            'location_id' => [
                'type' => 'long',
            ],
            'data'        => [
                'enabled' => false,
            ],
        ],
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'due_at'        => 'datetime:Y-m-d\TH:i:s\Z',
        'starts_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'ends_at'       => 'datetime:Y-m-d\TH:i:s\Z',
        'snoozed_until' => 'datetime:Y-m-d\TH:i:s\Z',
        'created_at'    => 'datetime:Y-m-d\TH:i:s\Z',
        'kpi_missed_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'due_at',
        'starts_at',
        'ends_at',
        'snoozed_until',
        'created_at',
        'kpi_missed_at',
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
     * Parent job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id')
            ->withTrashed();
    }

    /**
     * Task type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(JobTaskType::class, 'job_task_type_id');
    }

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
     * Assigned users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'job_task_crew_assignments',
            'job_task_id',
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
            'job_task_vehicle_assignments',
            'job_task_id',
            'vehicle_id'
        )->withPivot('assigner_id', 'created_at');
    }

    /**
     * Assigned teams.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedTeams(): BelongsToMany
    {
        return $this->belongsToMany(
            Team::class,
            'job_task_team_assignments',
            'job_task_id',
            'team_id'
        )->withPivot('assigner_id', 'created_at');
    }

    /**
     * Latest (or current) job task status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this
            ->hasOne(JobTaskStatus::class, 'job_task_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Scheduled portion of a latest (or current) task status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestScheduledStatus(): HasOne
    {
        return $this
            ->hasOne(JobTaskScheduledPortionStatus::class, 'job_task_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Job task statuses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this
            ->hasMany(JobTaskStatus::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Scheduled portion of task statuses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scheduledStatuses(): HasMany
    {
        return $this
            ->hasMany(JobTaskScheduledPortionStatus::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Search for tasks that overlaps the given time range.
     *
     * @param \Illuminate\Support\Carbon $startsAt Starting time.
     * @param \Illuminate\Support\Carbon $endsAt   Ending time.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function queryOverlaps(Carbon $startsAt, Carbon $endsAt): Builder
    {
        return self::query()->whereRaw(sprintf(
            "(TIMESTAMP '%s', TIMESTAMP '%s' - INTERVAL '1 seconds') 
                        OVERLAPS 
                    (starts_at, ends_at - INTERVAL '1 seconds')",
            $startsAt,
            $endsAt
        ));
    }

    /**
     * Allows to change status of this task.
     *
     * @param string      $status New task status.
     * @param int|null    $userId Optional id of user who is changing status.
     * @param string|null $reason Reason why status is changed.
     *
     * @return \App\Components\Jobs\Models\JobTaskStatus
     */
    public function changeStatus(string $status, int $userId = null, string $reason = null): JobTaskStatus
    {
        if (!in_array($status, JobTaskStatuses::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status %s specified, allowed values are: %s',
                $status,
                implode(',', JobTaskStatuses::values())
            ));
        }

        /** @var \App\Components\Jobs\Models\JobTaskStatus $createdStatus */
        $createdStatus = $this->statuses()->create([
            'status'  => $status,
            'reason'  => $reason,
            'user_id' => $userId,
        ]);

        return $createdStatus;
    }

    /**
     * Allows to change scheduled portion of this task's status.
     *
     * @param string      $status New task status.
     * @param int|null    $userId Optional id of user who is changing status.
     * @param string|null $reason Reason why status is changed.
     *
     * @return \App\Components\Jobs\Models\JobTaskScheduledPortionStatus
     */
    public function changeScheduledStatus(
        string $status,
        int $userId = null,
        string $reason = null
    ): JobTaskScheduledPortionStatus {
        if (!in_array($status, JobTaskStatuses::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status %s specified, allowed values are: %s',
                $status,
                implode(',', JobTaskStatuses::values())
            ));
        }

        /** @var \App\Components\Jobs\Models\JobTaskScheduledPortionStatus $createdStatus */
        $createdStatus = $this->scheduledStatuses()->create([
            'status'  => $status,
            'reason'  => $reason,
            'user_id' => $userId,
        ]);

        return $createdStatus;
    }

    /**
     * Setter for due_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return self
     * @throws \Throwable
     */
    public function setDueAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('due_at', $datetime);
    }

    /**
     * Setter for starts_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return self
     * @throws \Throwable
     */
    public function setStartsAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('starts_at', $datetime);
    }

    /**
     * Setter for ends_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return self
     * @throws \Throwable
     */
    public function setEndsAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('ends_at', $datetime);
    }

    /**
     * Setter for snoozed_until attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return self
     * @throws \Throwable
     */
    public function setSnoozedUntilAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('snoozed_until', $datetime);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $result['job_id']      = $this->job_id;
        $result['job_run_id']  = $this->job_run_id;
        $result['name']        = $this->name;
        $result['location_id'] = $this->job->assigned_location_id;
        $result['data']        = $this->toArray();

        $additionalData = [
            'type'              => $this->type,
            'assigned_users'    => $this->assignedUsers,
            'assigned_vehicles' => $this->assignedVehicles,
            'assigned_teams'    => $this->assignedTeams,
            'latest_status'     => $this->latestStatus,
        ];

        $additionalData['job'] = [
            'id'               => $this->job->id,
            'claim_number'     => $this->job->claim_number,
            'site_address'     => $this->job->siteAddress,
            'site_contact'     => $this->job->getSiteContact(),
            'site_address_lat' => $this->job->site_address_lat,
            'site_address_lng' => $this->job->site_address_lng,
        ];

        $result['data'] = array_merge($result['data'], $additionalData);

        return $result;
    }

    /**
     * Generate a query to retrieve tasks scheduled for a week.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Support\Carbon            $date
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeScheduledForWeek(Builder $query, Carbon $date): Builder
    {
        $startOfWeek = (clone $date)->startOfWeek();
        $endOfWeek   = (clone $date)->endOfWeek();

        return $query
            ->join('job_runs AS jr', 'job_run_id', '=', 'jr.id')
            ->whereBetween('date', [$startOfWeek, $endOfWeek]);
    }

    /**
     * Scope a query to include jobs tasks that should be un-snoozed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldBeUnsnoozed(Builder $query): Builder
    {
        return $query->where('snoozed_until', '<', 'now()');
    }
}
