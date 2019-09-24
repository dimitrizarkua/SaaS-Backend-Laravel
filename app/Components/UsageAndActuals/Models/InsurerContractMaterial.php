<?php

namespace App\Components\UsageAndActuals\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class InsurerContractMaterial
 *
 * @mixin \Eloquent
 *
 * @property int                  $insurer_contract_id
 * @property int                  $material_id
 * @property string|null          $name
 * @property float                $sell_cost_per_unit
 * @property int|null             $up_to_units
 * @property float|null           $up_to_amount
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 *
 * @property-read InsurerContract $insurerContract
 * @property-read Material        $material
 *
 * @method static \App\Components\UsageAndActuals\Models\InsurerContractMaterial whereInsurerContractId($value)
 * @method static \App\Components\UsageAndActuals\Models\InsurerContractMaterial whereMaterialId($value)
 * @method static \App\Components\UsageAndActuals\Models\InsurerContractMaterial whereName($value)
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "insurer_contract_id",
 *         "material_id",
 *         "sell_cost_per_unit",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class InsurerContractMaterial extends Model
{
    /**
     * @OA\Property(
     *     property="insurer_contract_id",
     *     type="integer",
     *     description="Insurer contract identifier",
     *     example=1
     * )
     * @OA\Property(property="material_id", type="integer", description="Material identifier", example=1)
     * @OA\Property(property="name", type="string", nullable=true)
     * @OA\Property(
     *     property="sell_cost_per_unit",
     *     type="number",
     *     format="float",
     *     description="Sell cost per unit",
     *     example="12.3"
     * )
     * @OA\Property(
     *     property="up_to_units",
     *     type="integer",
     *     nullable=true,
     *     description="Maximum number of units can be paid by the insurer",
     * )
     * @OA\Property(
     *     property="up_to_amount",
     *     type="number",
     *     nullable=true,
     *     description="Maximum amount can be paid by the insurer",
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     */

    use HasCompositePrimaryKey;

    public $primaryKey   = ['insurer_contract_id', 'material_id'];
    public $incrementing = false;
    public $timestamps   = true;

    protected $fillable = ['insurer_contract_id', 'material_id'];

    protected $table = 'insurer_contract_material';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'         => 'datetime:Y-m-d\TH:i:s\Z',
        'sell_cost_per_unit' => 'float',
        'up_to_amount'       => 'float',
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
     * Relationship with insurer_contracts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurerContract(): BelongsTo
    {
        return $this->belongsTo(InsurerContract::class, 'insurer_contract_id');
    }

    /**
     * Relationship with materials table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
