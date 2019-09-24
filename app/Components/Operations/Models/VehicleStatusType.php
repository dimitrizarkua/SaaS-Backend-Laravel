<?php

namespace App\Components\Operations\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * Class VehicleStatusType
 *
 * @mixin \Eloquent
 *
 * @property int                             $id
 * @property string                          $name
 * @property boolean                         $makes_vehicle_unavailable
 * @property boolean                         $is_default
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","name","makes_vehicle_unavailable","is_default"}
 * )
 */
class VehicleStatusType extends Model
{
    use ApiRequestFillable, SoftDeletes;

    public $timestamps = false;

    /**
     * @OA\Property(property="id", type="integer", description="Vehicle status type identifier", example=1)
     * @OA\Property(property="name", type="string", description="Name", example="Reserved")
     * @OA\Property(
     *     property="makes_vehicle_unavailable",
     *     type="boolean",
     *     description="Indicates that a vehicle with this status is unavailable for scheduling",
     *     example=true
     * )
     * @OA\Property(
     *     property="is_default",
     *     type="boolean",
     *     description="Indicates a vehicle default (initial) status",
     *     example=true
     * )
     * @OA\Property(property="deleted_at", type="string", format="date-time")
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
     * Define relationship with vehicle_statuses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(VehicleStatus::class, 'vehicle_status_type_id');
    }

    /**
     * Return a vehicle default (initial) status.
     *
     * @return self
     */
    public static function getDefaultStatus(): self
    {
        return self::query()->where('is_default', '=', 'true')->firstOrFail();
    }
}
