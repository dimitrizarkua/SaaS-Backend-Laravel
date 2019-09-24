<?php

namespace App\Components\AssessmentReports\Models;

use App\Models\ApiRequestFillable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * Class FlooringSubtype
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property int               $id
 * @property int               $flooring_type_id
 * @property string            $name
 * @property Carbon            $created_at
 * @property Carbon            $updated_at
 * @property Carbon|null       $deleted_at
 * @property-read FlooringType $flooringType
 *
 * @method static Builder|FlooringSubtype whereId($value)
 * @method static Builder|FlooringSubtype whereFlooringType($value)
 * @method static Builder|FlooringSubtype whereName($value)
 * @method static Builder|FlooringSubtype whereIsCreatedAt($value)
 * @method static Builder|FlooringSubtype whereIsUpdatedAt($value)
 * @method static Builder|FlooringSubtype whereIsADeletedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id", "flooring_type_id", "name", "created_at", "updated_at"}
 * )
 */
class FlooringSubtype extends Model
{
    use ApiRequestFillable, SoftDeletes;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Flooring subtype id",
     *     type="integer",
     *     example=1,
     * ),
     * /**
     * @OA\Property(
     *     property="flooring_type_id",
     *     description="Flooring type id",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Flooring subtype name",
     *     type="string",
     *     example="Laminate",
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
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at' => 'datetime:Y-m-d\TH:i:s\Z',
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
     * Relationship with flooring_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flooringType(): BelongsTo
    {
        return $this->belongsTo(FlooringType::class);
    }
}
