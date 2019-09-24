<?php

namespace App\Components\UsageAndActuals\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class MeasureUnit
 *
 * @property int           $id
 * @property string        $name
 * @property string        $code
 *
 * @property-read Material $materials
 *
 * @method static Builder|InsurerContract query()
 * @method static Builder|InsurerContract whereId($value)
 * @method static Builder|InsurerContract whereCode($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "name",
 *          "code",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class MeasureUnit extends Model
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
     *    property="name",
     *    description="Name of measure unit",
     *    type="string",
     * ),
     * @OA\Property(
     *    property="code",
     *    description="Code of measure unit",
     *    type="string",
     * ),
     */

    protected $touches = ['materials'];

    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Relationship with materials table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
