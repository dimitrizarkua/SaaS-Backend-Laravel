<?php

namespace App\Components\Jobs\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskType
 *
 * @mixin \Eloquent
 *
 * @property int                       $id
 * @property string                    $name
 * @property boolean                   $can_be_scheduled
 * @property boolean                   $allow_edit_due_date
 * @property integer                   $default_duration_minutes
 * @property integer|null              $kpi_hours
 * @property boolean                   $kpi_include_afterhours
 * @property integer|null              $color
 * @property Carbon|null               $deleted_at
 * @property boolean                   $auto_create
 *
 * @property-read Collection|JobTask[] $tasks
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "id",
 *         "name",
 *         "allow_edit_due_date",
 *         "can_be_scheduled",
 *         "default_duration_minutes",
 *         "kpi_include_afterhours",
 *         "auto_create"
 *     }
 * )
 */
class JobTaskType extends Model
{
    use ApiRequestFillable;

    public $timestamps = false;

    /**
     * @OA\Property(property="id", type="integer", description="Job task type identifier", example=1)
     * @OA\Property(property="name", type="string", description="Name", example="Equipment pickup")
     * @OA\Property(
     *     property="can_be_scheduled",
     *     type="boolean",
     *     description="Indicates whether a task with this type can be scheduled",
     *     example=true
     * )
     * @OA\Property(
     *     property="allow_edit_due_date",
     *     type="boolean",
     *     description="Allow user to edit the task’s due date",
     *     example=true
     * )
     * @OA\Property(
     *     property="default_duration_minutes",
     *     type="integer",
     *     description="Default task duration (in minutes)",
     *     example=120
     * )
     * @OA\Property(
     *     property="kpi_hours",
     *     type="integer",
     *     nullable=true,
     *     description="KPI hours",
     *     example=24
     * )
     * @OA\Property(
     *     property="kpi_include_afterhours",
     *     type="boolean",
     *     description="Indicates whether a task with this type can include afterhours",
     *     example=false
     * )
     * @OA\Property(
     *     property="color",
     *     type="integer",
     *     description="Defines the color of frames for tasks of this type",
     *     example=16777215,
     *     nullable=true,
     * )
     * @OA\Property(
     *     property="deleted_at",
     *     type="string",
     *     nullable=true,
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="auto_create",
     *     type="boolean",
     *     description="Indicates whether a task of this type should be created automatically when new job will be
    created",
     *     example=false
     * )
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'deleted_at',
    ];

    /**
     * Define relationship with job_tasks table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany(JobTask::class, 'job_task_type_id');
    }
}
