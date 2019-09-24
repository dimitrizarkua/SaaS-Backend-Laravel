<?php

namespace App\Components\UsageAndActuals\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class InsurerContractLabourType
 *
 * @mixin \Eloquent
 *
 * @property int                  $insurer_contract_id
 * @property int                  $labour_type_id
 * @property string|null          $name
 * @property float                $first_tier_hourly_rate
 * @property float                $second_tier_hourly_rate
 * @property float                $third_tier_hourly_rate
 * @property float                $fourth_tier_hourly_rate
 * @property int|null             $up_to_hours
 * @property float|null           $up_to_amount
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 *
 * @property-read InsurerContract $insurerContract
 * @property-read LabourType      $labourType
 *
 * @method static InsurerContractMaterial whereInsurerContractId($value)
 * @method static InsurerContractMaterial whereLabourTypeId($value)
 * @method static InsurerContractMaterial whereName($value)
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "insurer_contract_id",
 *         "labour_type_id",
 *         "first_tier_hourly_rate",
 *         "second_tier_hourly_rate",
 *         "third_tier_hourly_rate",
 *         "fourth_tier_hourly_rate",
 *         "created_at",
 *         "updated_at",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class InsurerContractLabourType extends Model
{
    /**
     * @OA\Property(
     *     property="insurer_contract_id",
     *     type="integer",
     *     description="Insurer contract identifier",
     *     example=1
     * ),
     * @OA\Property(property="labour_type_id", type="integer", description="Labour type identifier", example=1),
     * @OA\Property(property="name", type="string", nullable=true),
     * @OA\Property(
     *    property="first_tier_hourly_rate",
     *    description="First tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="second_tier_hourly_rate",
     *    description="Second tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="third_tier_hourly_rate",
     *    description="Third tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="fourth_tier_hourly_rate",
     *    description="Fourth tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *     property="up_to_hours",
     *     type="integer",
     *     nullable=true,
     *     description="Maximum number of hours can be paid by the insurer",
     * ),
     * @OA\Property(
     *     property="up_to_amount",
     *     type="number",
     *     format="float",
     *     nullable=true,
     *     description="Maximum amount can be paid by the insurer",
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     */

    use HasCompositePrimaryKey;

    public $primaryKey   = ['insurer_contract_id', 'labour_type_id'];
    public $incrementing = false;
    public $timestamps   = true;

    protected $fillable = ['insurer_contract_id', 'labour_type_id'];

    protected $table = 'insurer_contract_labour_types';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'first_tier_hourly_rate'  => 'float',
        'second_tier_hourly_rate' => 'float',
        'third_tier_hourly_rate'  => 'float',
        'fourth_tier_hourly_rate' => 'float',
        'up_to_amount'            => 'float',
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
     * Relationship with labour_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function labourType(): BelongsTo
    {
        return $this->belongsTo(LabourType::class, 'labour_type_id');
    }
}
