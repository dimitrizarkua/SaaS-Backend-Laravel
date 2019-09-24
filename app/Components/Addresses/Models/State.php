<?php

namespace App\Components\Addresses\Models;

use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * Class State
 *
 * @property int                                                                                     $id
 * @property int                                                                                     $country_id
 * @property string                                                                                  $name
 * @property string                                                                                  $code
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\State whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\State whereName($value)
 * @mixin \Eloquent
 * @property-read \App\Components\Addresses\Models\Country                                           $country
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Components\Addresses\Models\Suburb[] $suburbs
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","country_id","name","code"}
 * )
 */
class State extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="State Identifier",
     *     type="integer",
     *     example="1"
     * )
     * @OA\Property(
     *     property="country_id",
     *     description="Country identifier",
     *     type="integer",
     *     example="1"
     * )
     * @OA\Property(
     *     property="name",
     *     description="State name",
     *     type="string",
     *     example="New South Wales"
     * ),
     * @OA\Property(
     *     property="code",
     *     description="State code",
     *     type="string",
     *     example="NSW"
     * )
     */

    public $timestamps = false;
    protected $guarded = ['id'];

    /**
     * Define relationship with suburbs table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function suburbs()
    {
        return $this->hasMany(Suburb::class);
    }

    /**
     * Define relationship with countries table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
