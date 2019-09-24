<?php

namespace App\Components\Addresses\Models;

use App\Components\Addresses\SuburbsIndexConfigurator;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ScoutElastic\Searchable;

/**
 * Class Suburb
 *
 * @property int                                                                                      $id
 * @property int                                                                                      $state_id
 * @property string                                                                                   $name
 * @property string                                                                                   $postcode
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\Suburb whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\Suburb whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\Suburb wherePostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Addresses\Models\Suburb whereStateId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Components\Addresses\Models\Address[] $addresses
 * @property-read \App\Components\Addresses\Models\State                                              $state
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","state_id","name","postcode"}
 * )
 *
 */
class Suburb extends Model
{
    use ApiRequestFillable, Searchable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Suburb Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="state_id",
     *     description="State identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="name",
     *     description="Suburb name",
     *     type="string",
     *     example="Aarons Pass"
     * ),
     * @OA\Property(
     *     property="postcode",
     *     description="Suburb postcode",
     *     type="string",
     *     example="2850"
     * ),
     */

    public $timestamps = false;

    protected $guarded = ['id'];

    /**
     * Index configurator.
     *
     * @var string
     */
    protected $indexConfigurator = SuburbsIndexConfigurator::class;

    /**
     * Mapping for the model.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'state_id' => [
                'type' => 'long',
            ],
            'name'     => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
            ],
            'postcode' => [
                'type' => 'keyword',
            ],
        ],
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Allows to filter tags
     *
     * @param array $options
     *
     * @return \Laravel\Scout\Builder
     */
    public static function filter(array $options)
    {
        if (!isset($options['term'])) {
            $options['term'] = '*';
        }

        $query = static::search($options['term']);

        if (isset($options['state_id'])) {
            $query->where('state_id', $options['state_id']);
        }

        if (isset($options['count'])) {
            $query->take($options['count']);
        }

        return $query;
    }
}
