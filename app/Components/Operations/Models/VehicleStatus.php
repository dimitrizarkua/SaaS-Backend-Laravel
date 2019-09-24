<?php

namespace App\Components\Operations\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class VehicleStatus
 *
 * @mixin \Eloquent
 *
 * @property int                                                      $id
 * @property int                                                      $vehicle_id
 * @property int                                                      $vehicle_status_type_id
 * @property int                                                      $user_id
 * @property \Illuminate\Support\Carbon                               $created_at
 *
 * @property-read \App\Components\Operations\Models\Vehicle           $vehicle
 * @property-read \App\Components\Operations\Models\VehicleStatusType $type
 * @property-read \App\Models\User                                    $user
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","vehicle_id","vehicle_status_type_id","user_id","created_at"}
 * )
 */
class VehicleStatus extends Model
{
    const UPDATED_AT = null;

    public $timestamps = true;

    /**
     * @OA\Property(property="id", type="integer", description="Vehicle status identifier", example=1)
     * @OA\Property(property="vehicle_id", type="integer", description="Vehicle identifier", example=1)
     * @OA\Property(
     *     property="vehicle_status_type_id",
     *     type="integer",
     *     description="Vehicle status type identifier",
     *     example=1
     * )
     * @OA\Property(property="user_id", type="integer", description="User identifier", example=1)
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * Parent vehicle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Status type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleStatusType::class, 'vehicle_status_type_id');
    }

    /**
     * Creator user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
