<?php

namespace App\Components\Jobs\Models;

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\Models\EquipmentCategoryChargingInterval;
use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class JobEquipmentChargingInterval
 *
 * @property int                                    $job_equipment_id
 * @property int                                    $equipment_category_charging_interval_id
 * @property string                                 $charging_interval
 * @property float                                  $charging_rate_per_interval
 * @property int                                    $max_count_to_the_next_interval
 * @property float                                  $up_to_amount
 * @property int                                    $up_to_interval_count
 * @property-read JobEquipment                      $jobEquipment
 * @property-read EquipmentCategoryChargingInterval $chargingInterval
 *
 * @method static Builder|JobEquipmentChargingInterval newModelQuery()
 * @method static Builder|JobEquipmentChargingInterval newQuery()
 * @method static Builder|JobEquipmentChargingInterval query()
 * @method static Builder|JobEquipmentChargingInterval whereId($value)
 * @method static Builder|JobEquipmentChargingInterval whereEquipmentCategoryChargingIntervalId($value)
 * @method static Builder|JobEquipmentChargingInterval whereChargingInterval($value)
 * @method static Builder|JobEquipmentChargingInterval whereChargingRatePerInterval($value)
 * @method static Builder|JobEquipmentChargingInterval whereMaxCountToTheNextInterval($value)
 * @method static Builder|JobEquipmentChargingInterval whereUpToAmount($value)
 * @method static Builder|JobEquipmentChargingInterval whereUpToIntervalCount($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *         "job_equipment_id",
 *         "equipment_category_charging_interval_id",
 *         "charging_interval",
 *         "charging_rate_per_interval",
 *         "max_count_to_the_next_interval",
 *     }
 * )
 *
 * @package App\Components\Jobs\Models
 */
class JobEquipmentChargingInterval extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @OA\Property(
     *     property="job_equipment_id",
     *     description="Charging interval job equipment identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="equipment_category_charging_interval_id",
     *     description="Equipment category charging interval identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="charging_interval",
     *     description="Name of charging interval",
     *     type="string",
     *     example="Week",
     * ),
     * @OA\Property(
     *     property="charging_rate_per_interval",
     *     description="Charging rate per interval",
     *     type="number",
     *     format="float",
     *     example=50.85,
     * ),
     * @OA\Property(
     *     property="max_count_to_the_next_interval",
     *     description="Max count to the next interval",
     *     type="integer",
     *     default=0,
     *     example=1,
     * ),
     * @OA\Property(
     *     property="up_to_amount",
     *     description="Up to amount",
     *     type="number",
     *     format="float",
     *     example=40.25,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="up_to_interval_count",
     *     description="Up to interval count",
     *     type="integer",
     *     example=2,
     *     nullable=true,
     * ),
     */

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_equipment_charging_interval';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = [
        'job_equipment_id',
        'equipment_category_charging_interval_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'job_equipment_id',
        'equipment_category_charging_interval_id',
        'charging_interval',
        'charging_rate_per_interval',
        'max_count_to_the_next_interval',
        'up_to_amount',
        'up_to_interval_count',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'charging_rate_per_interval' => 'float',
        'up_to_amount'               => 'float',
    ];

    /**
     * Relationship with job_equipment table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jobEquipment(): BelongsTo
    {
        return $this->belongsTo(JobEquipment::class);
    }

    /**
     * Relationship with equipment_category_charging_intervals table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chargingInterval(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategoryChargingInterval::class);
    }

    /**
     * Indicates that other interval (week) should be chosen instead of this (day interval).
     *
     * @param int $intervalCount
     *
     * @return bool
     */
    public function shouldSelectOtherInterval(int $intervalCount): bool
    {
        return $this->charging_interval === EquipmentCategoryChargingIntervals::DAY
            && $this->max_count_to_the_next_interval > 0
            && $intervalCount > $this->max_count_to_the_next_interval;
    }
}
