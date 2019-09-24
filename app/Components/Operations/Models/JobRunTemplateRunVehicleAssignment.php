<?php

namespace App\Components\Operations\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class JobRunTemplateRunVehicleAssignment
 *
 * @mixin \Eloquent
 *
 * @property int                                                      $job_run_template_run_id
 * @property int                                                      $vehicle_id
 *
 * @property-read \App\Components\Operations\Models\JobRunTemplateRun $templateRun
 * @property-read \App\Components\Operations\Models\Vehicle           $vehicle
 *
 * @OA\Schema(
 *     type="object",
 *     required={"job_run_template_run_id","vehicle_id"}
 * )
 */
class JobRunTemplateRunVehicleAssignment extends Model
{
    use HasCompositePrimaryKey;

    protected $primaryKey = ['job_run_template_run_id', 'vehicle_id'];

    public $incrementing = false;
    public $timestamps   = false;

    /**
     * @OA\Property(property="job_run_template_run_id", type="integer", description="Run identifier", example=1)
     * @OA\Property(property="vehicle_id", type="integer", description="Assigned vehicle identifier", example=1)
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_run_template_run_id',
        'vehicle_id',
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
     * Assigned vehicle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
