<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Jobs\Models\JobAllowance;
use App\Components\Locations\Models\Location;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class AllowanceType
 *
 * @property int                            $id
 * @property int                            $location_id
 * @property string                         $name
 * @property float                          $charge_rate_per_interval
 * @property string                         $charging_interval
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 *
 * @property-read Location                  $location
 * @property-read Collection|JobAllowance[] $jobAllowances
 *
 * @method static Builder|InsurerContract query()
 * @method static Builder|InsurerContract whereCreatedAt($value)
 * @method static Builder|InsurerContract whereId($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "location_id",
 *          "name",
 *          "charge_rate_per_interval",
 *          "charging_interval",
 *          "created_at",
 *          "updated_at",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class AllowanceType extends Model
{
    use ApiRequestFillable;
    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="location_id",
     *    description="Location identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="name",
     *    description="Name of allowance type",
     *    type="string",
     * ),
     * @OA\Property(
     *    property="charge_rate_per_interval",
     *    description="Charge rate per interval",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="charging_interval",
     *    description="Charging interval",
     *    ref="#/components/schemas/AllowanceTypeChargingIntervals"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relationship with location table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }


    /**
     * Relationship with job_allowances table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobAllowances(): HasMany
    {
        return $this->hasMany(JobAllowance::class);
    }
}
