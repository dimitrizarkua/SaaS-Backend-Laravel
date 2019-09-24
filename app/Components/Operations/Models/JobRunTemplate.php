<?php

namespace App\Components\Operations\Models;

use App\Components\Locations\Models\Location;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * Class JobRunTemplate
 *
 * @mixin \Eloquent
 *
 * @property int                                                     $id
 * @property int                                                     $location_id
 * @property string|null                                             $name
 * @property \Illuminate\Support\Carbon                              $created_at
 * @property \Illuminate\Support\Carbon|null                         $deleted_at
 *
 * @property-read \App\Components\Locations\Models\Location          $location
 * @property-read \Illuminate\Support\Collection|JobRunTemplateRun[] $runs
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","location_id","created_at"}
 * )
 */
class JobRunTemplate extends Model
{
    use SoftDeletes, ApiRequestFillable;

    const UPDATED_AT = null;

    public $timestamps = true;

    /**
     * @OA\Property(property="id", type="integer", description="Template identifier", example=1)
     * @OA\Property(property="location_id", type="integer", description="Location identifier", example=1)
     * @OA\Property(property="name", type="string", description="Name", example="New Template")
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="deleted_at", type="string", format="date-time")
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'deleted_at' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'deleted_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'deleted_at',
    ];

    /**
     * Location that the template belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Template runs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function runs(): HasMany
    {
        return $this->hasMany(JobRunTemplateRun::class, 'job_run_template_id');
    }
}
