<?php

namespace App\Components\Finance\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * Class TaxRate
 *
 * @property int                         $id
 * @property string                      $name
 * @property float                       $rate
 * @property-read Collection|GLAccount[] $glAccounts
 *
 * @method static Builder|TaxRate newModelQuery()
 * @method static Builder|TaxRate newQuery()
 * @method static Builder|TaxRate query()
 * @method static Builder|TaxRate whereId($value)
 * @method static Builder|TaxRate whereName($value)
 * @method static Builder|TaxRate whereRate($value)
 *
 * @OA\Schema(
 *     required={"id","name","rate"}
 * )
 *
 * @mixin \Eloquent
 */
class TaxRate extends Model
{
    use ApiRequestFillable;

    public $timestamps = false;

    protected $guarded = ['id'];
    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="name",
     *    description="Name",
     *    type="string",
     *    example="GST on Income"
     * ),
     * @OA\Property(
     *    property="rate",
     *    type="number",
     *    example=0.1
     * )
     */

    protected $casts = [
        'rate' => 'float',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function glAccounts(): HasMany
    {
        return $this->hasMany(GLAccount::class);
    }
}
