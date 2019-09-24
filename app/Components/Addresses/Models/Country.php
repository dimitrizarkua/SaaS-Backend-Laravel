<?php

namespace App\Components\Addresses\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class Country
 *
 * @property int                     $id
 * @property string                  $name
 * @property string                  $iso_alpha2_code
 * @property string                  $iso_alpha3_code
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereIsoAlpha2Code($value)
 * @method static Builder|Country whereIsoAlpha3Code($value)
 * @method static Builder|Country whereName($value)
 * @mixin \Eloquent
 * @property-read Collection|State[] $states
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","name","iso_alpha2_code","iso_alpha3_code"}
 * )
 *
 */
class Country extends Model
{
    use ApiRequestFillable;

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * @OA\Property(
     *     property="id",
     *     description="CountryIdentifier",
     *     type="integer",
     *     example="1"
     * )
     * @OA\Property(
     *     property="name",
     *     description="Country name",
     *     type="string",
     *     example="Australia"
     * )
     * @OA\Property(
     *     property="iso_alpha2_code",
     *     description="Two-letter country code",
     *     type="string",
     *     example="AU"
     * ),
     * @OA\Property(
     *     property="iso_alpha3_code",
     *     description="Three-letter country code",
     *     type="string",
     *     example="AUS"
     * )
     */

    /**
     * Define relationship with states table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }
}
