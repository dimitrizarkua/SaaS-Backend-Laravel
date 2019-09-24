<?php

namespace App\Components\UsageAndActuals\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentCategoryChargingInterval
 *
 * @property int                                   $id
 * @property int                                   $equipment_category_id
 * @property string                                $charging_interval
 * @property float                                 $charging_rate_per_interval
 * @property int                                   $max_count_to_the_next_interval
 * @property bool                                  $is_default
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 * @property-read EquipmentCategory                $category
 * @property-read EquipmentCategoryInsurerContract $insurerContract
 *
 * @method static Builder|EquipmentCategoryChargingInterval newModelQuery()
 * @method static Builder|EquipmentCategoryChargingInterval newQuery()
 * @method static Builder|EquipmentCategoryChargingInterval query()
 * @method static Builder|EquipmentCategoryChargingInterval whereId($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereEquipmentCategoryId($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereChargingInterval($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereChargingRatePerInterval($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereMaxCountToTheNextInterval($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereIsDefault($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereCreatedAt($value)
 * @method static Builder|EquipmentCategoryChargingInterval whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "equipment_category_id",
 *         "charging_interval",
 *         "charging_rate_per_interval",
 *         "max_count_to_the_next_interval",
 *         "is_default",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class EquipmentCategoryChargingInterval extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Equipment category charging interval identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="equipment_category_id",
     *     description="Equipment category identifier",
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
     *     property="is_default",
     *     description="Indicates whether charging interval is default or not",
     *     type="boolean",
     *     default=false,
     *     example=true,
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
        'charging_rate_per_interval' => 'float',
        'created_at'                 => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'                 => 'datetime:Y-m-d\TH:i:s\Z',
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
     * Equipment category in which the charging interval is used.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class);
    }

    /**
     * Insurer contracts in which the category is used.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function insurerContract(): HasOne
    {
        return $this->hasOne(EquipmentCategoryInsurerContract::class);
    }
}
