<?php

namespace App\Components\UsageAndActuals\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentCategoryInsurerContract
 *
 * @property int                                    $insurer_contract_id
 * @property int                                    $equipment_category_charging_interval_id
 * @property string|null                            $name
 * @property float|null                             $up_to_amount
 * @property int|null                               $up_to_interval_count
 * @property Carbon                                 $created_at
 * @property Carbon                                 $updated_at
 * @property-read EquipmentCategoryChargingInterval $chargingInterval
 * @property-read InsurerContract                   $insurerContract
 *
 * @method static Builder|EquipmentCategory newModelQuery()
 * @method static Builder|EquipmentCategory newQuery()
 * @method static Builder|EquipmentCategory query()
 * @method static Builder|EquipmentCategory whereInsurerContractId($value)
 * @method static Builder|EquipmentCategory whereEquipmentCategoryChargingIntervalId($value)
 * @method static Builder|EquipmentCategory whereName($value)
 * @method static Builder|EquipmentCategory whereUpToAmount($value)
 * @method static Builder|EquipmentCategory whereUpToIntervalCount($value)
 * @method static Builder|EquipmentCategory whereCreatedAt($value)
 * @method static Builder|EquipmentCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *         "insurer_contract_id",
 *         "equipment_category_charging_interval_id",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class EquipmentCategoryInsurerContract extends Model
{
    use HasCompositePrimaryKey;

    /**
     * @OA\Property(
     *     property="insurer_contract_id",
     *     description="Insurer contract identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="equipment_category_charging_interval_id",
     *     description="Equipment category charging intreval identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Name of equipment category for insurer contract",
     *     type="string",
     *     example="Robotic Duct Cleaning Equipment",
     *     nullable=true,
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
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
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
    protected $table = 'equipment_category_insurer_contract';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = [
        'insurer_contract_id',
        'equipment_category_charging_interval_id',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'up_to_amount' => 'float',
        'created_at'   => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'   => 'datetime:Y-m-d\TH:i:s\Z',
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
     * Relationship with equipment_category_charging_intervals table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chargingInterval(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategoryChargingInterval::class, 'equipment_category_charging_interval_id');
    }

    /**
     * Relationship with insurer_contracts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurerContract(): BelongsTo
    {
        return $this->belongsTo(InsurerContract::class, 'insurer_contract_id');
    }
}
