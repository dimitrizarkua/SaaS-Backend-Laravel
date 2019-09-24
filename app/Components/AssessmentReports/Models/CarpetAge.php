<?php

namespace App\Components\AssessmentReports\Models;

use App\Models\ApiRequestFillable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * Class CarpetAge
 *
 * @package App\Components\AssessmentReports\Models
 *
 * @property int         $id
 * @property string      $name
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|CarpetAge whereId($value)
 * @method static Builder|CarpetAge whereName($value)
 * @method static Builder|CarpetAge whereIsCreatedAt($value)
 * @method static Builder|CarpetAge whereIsUpdatedAt($value)
 * @method static Builder|CarpetAge whereIsADeletedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id", "name", "created_at", "updated_at"}
 * )
 */
class CarpetAge extends Model
{
    use ApiRequestFillable, SoftDeletes;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Carpet age id",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Carpet age name",
     *     type="string",
     *     example="1-3",
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
}
