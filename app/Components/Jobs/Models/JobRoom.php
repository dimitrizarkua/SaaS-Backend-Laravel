<?php

namespace App\Components\Jobs\Models;

use App\Components\AssessmentReports\Models\FlooringType;
use App\Models\ApiRequestFillable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * Class JobRoom
 *
 * @property int               $id
 * @property int               $job_id
 * @property int|null          $flooring_type_id
 * @property string            $name
 * @property float|null        $total_sqm
 * @property float|null        $affected_sqm
 * @property float|null        $non_restorable_sqm
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 * @property Carbon|null       $deleted_at
 * @property-read Job          $job
 * @property-read FlooringType $flooringType
 *
 * @method static Builder|JobRoom whereId($value)
 * @method static Builder|JobRoom whereJobId($value)
 * @method static Builder|JobRoom whereFlooringTypeId($value)
 * @method static Builder|JobRoom whereName($value)
 * @method static Builder|JobRoom whereTotalSqm($value)
 * @method static Builder|JobRoom whereAffectedSqm$value)
 * @method static Builder|JobRoom whereNonRestorableSqm($value)
 * @method static Builder|JobRoom whereCreatedAt($value)
 * @method static Builder|JobRoom whereUpdatedAt($value)
 * @method static Builder|JobRoom whereDeletedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"job_id", "name", "created_at", "updated_at"}
 * )
 */
class JobRoom extends Model
{
    use ApiRequestFillable, SoftDeletes;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Job room identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="job_id",
     *     description="Job identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="flooring_type_id",
     *     description="Flooring type identifier",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Job room name",
     *     type="string",
     *     example="Lounge",
     * ),
     * @OA\Property(
     *     property="total_sqm",
     *     description="Job room total area in square meters",
     *     type="number",
     *     format="float",
     *     example=30,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="affected_sqm",
     *     description="Job room affected area in square meters",
     *     type="number",
     *     format="float",
     *     example=20,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="non_restorable_sqm",
     *     description="Job room non-restorable area in square meters",
     *     type="number",
     *     format="float",
     *     example=5,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time",
     * ),
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time",
     * ),
     * @OA\Property(
     *     property="deleted_at",
     *     type="string",
     *     format="date-time",
     *     nullable=true,
     * ),
     */

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'total_sqm'          => 'float',
        'affected_sqm'       => 'float',
        'non_restorable_sqm' => 'float',
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
    ];

    /**
     * Associated flooring type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flooringType(): BelongsTo
    {
        return $this->belongsTo(FlooringType::class);
    }

    /**
     * Associated job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
