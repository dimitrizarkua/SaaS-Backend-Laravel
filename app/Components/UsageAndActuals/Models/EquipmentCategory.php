<?php

namespace App\Components\UsageAndActuals\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentCategory
 *
 * @property int                                                 $id
 * @property string                                              $name
 * @property boolean                                             $is_airmover
 * @property boolean                                             $is_dehum
 * @property float                                               $default_buy_cost_per_interval
 * @property-read Collection|EquipmentCategoryChargingInterval[] $chargingIntervals
 * @property-read Collection|EquipmentCategoryChargingInterval[] $defaultChargingIntervals
 * @property-read Collection|Equipment[]                         $equipment
 *
 * @method static Builder|EquipmentCategory newModelQuery()
 * @method static Builder|EquipmentCategory newQuery()
 * @method static Builder|EquipmentCategory query()
 * @method static Builder|EquipmentCategory whereId($value)
 * @method static Builder|EquipmentCategory whereName($value)
 * @method static Builder|EquipmentCategory whereIsAirmover($value)
 * @method static Builder|EquipmentCategory whereIsDehum($value)
 * @method static Builder|EquipmentCategory whereDefaultBuyCostPerInterval($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "name",
 *         "is_airmover",
 *         "is_dehum",
 *         "default_buy_cost_per_interval",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class EquipmentCategory extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Equipment category identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Name of equipment category",
     *     type="string",
     *     example="Blasting Major Loss Kit",
     * ),
     * @OA\Property(
     *     property="is_airmover",
     *     description="Defines whether is it airmover",
     *     type="boolean",
     *     default=false,
     *     example=true,
     * ),
     * @OA\Property(
     *     property="is_dehum",
     *     description="Defines whether is it dehumidifier",
     *     type="boolean",
     *     default=false,
     *     example=false,
     * ),
     * @OA\Property(
     *     property="default_buy_cost_per_inteval",
     *     description="Default buy cost per interval",
     *     type="number",
     *     format="float",
     *     example=50.85,
     * ),
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    protected $fillable = [
        'name',
        'is_airmover',
        'is_dehum',
        'default_buy_cost_per_interval',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'default_buy_cost_per_interval' => 'float',
    ];

    /**
     * All charging intervals for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chargingIntervals(): HasMany
    {
        return $this->hasMany(EquipmentCategoryChargingInterval::class)
            ->orderBy('id');
    }

    /**
     * Equipment which the category has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    /**
     * Default charging intervals for the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function defaultChargingIntervals(): HasMany
    {
        return $this->chargingIntervals()->where('is_default', true);
    }
}
