<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Jobs\Models\Job;
use App\Components\UsageAndActuals\MaterialsIndexConfigurator;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use ScoutElastic\Searchable;

/**
 * Class Material
 *
 * @property int                               $id
 * @property string|null                       $name
 * @property int                               $measure_unit_id
 * @property float                             $default_sell_cost_per_unit
 * @property float                             $default_buy_cost_per_unit
 * @property Carbon                            $created_at
 * @property Carbon                            $updated_at
 *
 * @property-read MeasureUnit                  $measureUnit
 * @property-read Collection|Job[]             $jobs
 * @property-read Collection|InsurerContract[] $insurerContracts
 *
 * @method static Builder|InsurerContract query()
 * @method static Builder|InsurerContract whereCreatedAt($value)
 * @method static Builder|InsurerContract whereId($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "measure_unit_id",
 *          "default_sell_cost_per_unit",
 *          "default_buy_cost_per_unit",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class Material extends Model
{
    use ApiRequestFillable, Searchable;
    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="name",
     *    description="Name of material",
     *    type="string",
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="measure_unit_id",
     *    description="Identifier of measure unit",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="default_sell_cost_per_unit",
     *    description="Default sell cost per unit",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="default_buy_cost_per_unit",
     *    description="Default buy cost per unit",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     */

    protected $indexConfigurator = MaterialsIndexConfigurator::class;

    /**
     * Mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'id'           => [
                'type' => 'long',
            ],
            'name'         => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
            ],
            'measure_unit' => [
                'enabled' => false,
            ],
        ],
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $result                 = $this->toArray();
        $result['measure_unit'] = $this->measureUnit;

        return $result;
    }

    public $timestamps = true;

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
        'created_at'                 => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'                 => 'datetime:Y-m-d\TH:i:s\Z',
        'default_sell_cost_per_unit' => 'float',
        'default_buy_cost_per_unit'  => 'float',
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
     * Relationship with jobs table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Job::class,
                'job_material',
                'material_id',
                'job_id'
            );
    }

    /**
     * Relationship with insurer_contracts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function insurerContracts(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                InsurerContract::class,
                'insurer_contract_material',
                'material_id',
                'insurer_contract_id'
            );
    }

    /**
     * Relationship with measure_unit table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measureUnit(): BelongsTo
    {
        return $this->belongsTo(MeasureUnit::class, 'measure_unit_id');
    }
}
