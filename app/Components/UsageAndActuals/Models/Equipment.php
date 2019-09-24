<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Components\Notes\Models\Note;
use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Components\UsageAndActuals\EquipmentIndexConfigurator;
use App\Components\UsageAndActuals\EquipmentSearchRules;
use App\Components\UsageAndActuals\Resources\EquipmentResource;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class Equipment
 *
 * @property int                    $id
 * @property string                 $barcode
 * @property int                    $equipment_category_id
 * @property int|null               $location_id
 * @property string                 $make
 * @property string                 $model
 * @property string                 $serial_number
 * @property Carbon                 $created_at
 * @property Carbon                 $updated_at
 * @property Carbon|null            $deleted_at
 * @property Carbon|null            $last_test_tag_at
 * @property-read EquipmentCategory $category
 * @property-read Location|null     $location
 * @property-read Collection|Job[]  $jobs
 * @property-read Collection|Note[] $notes
 *
 * @method static Builder|EquipmentCategory newModelQuery()
 * @method static Builder|EquipmentCategory newQuery()
 * @method static Builder|EquipmentCategory query()
 * @method static Builder|EquipmentCategory whereId($value)
 * @method static Builder|EquipmentCategory whereBarcode($value)
 * @method static Builder|EquipmentCategory whereEquipmentCategoryId($value)
 * @method static Builder|EquipmentCategory whereLocationId($value)
 * @method static Builder|EquipmentCategory whereMake($value)
 * @method static Builder|EquipmentCategory whereModel($value)
 * @method static Builder|EquipmentCategory whereSerialNumber($value)
 * @method static Builder|EquipmentCategory whereCreatedAt($value)
 * @method static Builder|EquipmentCategory whereUpdatedAt($value)
 * @method static Builder|EquipmentCategory whereDeletedAt($value)
 * @method static Builder|EquipmentCategory whereLastTestTagAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "barcode",
 *         "equipment_category_id",
 *         "make",
 *         "model",
 *         "serial_number",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class Equipment extends Model
{
    use ApiRequestFillable, DateTimeFillable, SoftDeletes, Searchable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Equipment identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="barcode",
     *     description="Barcode",
     *     type="string",
     *     example="978-0-9542246",
     * ),
     * @OA\Property(
     *     property="equipment_category_id",
     *     description="Equipment category identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="location_id",
     *     description="Location identifier",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="make",
     *     description="Manufacturer",
     *     type="string",
     *     example="DampRid",
     * ),
     * @OA\Property(
     *     property="model",
     *     description="Specific model",
     *     type="string",
     *     example="FG90 Moisture Absorber Easy-Fill",
     * ),
     * @OA\Property(
     *     property="serial_number",
     *     description="Serial number",
     *     type="string",
     *     example="4CE0460D0G",
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     * @OA\Property(
     *     property="last_test_tag_at",
     *     description="Last test date",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z",
     *     nullable=true,
     * ),
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'       => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'       => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'       => 'datetime:Y-m-d\TH:i:s\Z',
        'last_test_tag_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'last_test_tag_at',
    ];

    /**
     * Elasticsearch index.
     */
    protected $indexConfigurator = EquipmentIndexConfigurator::class;

    /**
     * Search rules for model.
     *
     * @var array
     */
    protected $searchRules = [
        EquipmentSearchRules::class,
    ];

    /**
     * Elasticsearch mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'make'          => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
                'fielddata'       => true,
            ],
            'model'         => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
                'fielddata'       => true,
            ],
            'barcode'       => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete',
                'fielddata'       => true,
            ],
            'serial_number' => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete',
                'fielddata'       => true,
            ],
            'category_name' => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
                'fielddata'       => true,
            ],
            'location_id'   => [
                'type' => 'long',
            ],
            'data'          => [
                'enabled' => false,
            ],
        ],
    ];

    /**
     * Equipment category which to equipment belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }

    /**
     * Location which to equipment belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Jobs in which equipment is used.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(
            Job::class,
            'job_equipment',
            'equipment_id',
            'job_id'
        );
    }

    /**
     * Notes which attached to this equipment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(
            Note::class,
            'equipment_note',
            'equipment_id',
            'note_id'
        );
    }

    /**
     * Setter for last_test_tag_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return \App\Components\UsageAndActuals\Models\Equipment
     *
     * @throws \Throwable
     */
    public function setLastTestTagAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('last_test_tag_at', $datetime);
    }

    /**
     * Returns default charging interval for equipment
     *
     * @return null|EquipmentCategoryChargingInterval
     */
    public function getDefaultChargingInterval(): ?EquipmentCategoryChargingInterval
    {
        $chargingIntervals = $this->category->chargingIntervals()
            ->where('is_default', true)
            ->get();

        return self::getCommonInterval($chargingIntervals);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $result['make']          = $this->make;
        $result['model']         = $this->model;
        $result['barcode']       = $this->barcode;
        $result['serial_number'] = $this->serial_number;
        $result['category_name'] = $this->category->name;
        $result['location_id']   = $this->location_id ?? 0;

        $result['data'] = EquipmentResource::make($this);

        return $result;
    }

    /**
     * Allows to search entities on make, model, barcode, serial number or category name.
     *
     * @param array $options           Array that should contain term.
     * @param int   $locationId        Location identifier.
     * @param int   $insurerContractId Insurer contract identifier.
     *
     * @return Collection
     */
    public static function searchOnName(
        array $options,
        int $locationId = null,
        int $insurerContractId = null
    ): Collection {
        $locationIds[] = 0; //to search for equipment which don't belong to any location
        if (null !== $locationId) {
            $locationIds[] = $locationId;
        }
        $raw = static::search($options)
            ->whereIn('location_id', $locationIds)
            ->take(10)
            ->raw();

        $equipment = collect(mapElasticResults($raw))->pluck('data');
        if (null !== $insurerContractId) {
            $equipmentCategoryIds      = $equipment->pluck('equipment_category_id')
                ->unique();
            $contractChargingIntervals = EquipmentCategoryChargingInterval::query()
                ->whereIn('equipment_category_id', $equipmentCategoryIds)
                ->whereHas('insurerContract', function (Builder $query) use ($insurerContractId) {
                    return $query->where('insurer_contract_id', $insurerContractId);
                })
                ->get()
                ->groupBy('equipment_category_id');

            $equipment = $equipment->map(function ($equipment) use ($contractChargingIntervals) {
                if ($contractChargingIntervals->has($equipment['equipment_category_id'])) {
                    $chargingIntervals = $contractChargingIntervals->get($equipment['equipment_category_id']);

                    $equipment['charging_interval'] = self::getCommonInterval($chargingIntervals);
                }

                return $equipment;
            });
        }

        return $equipment;
    }

    /**
     * Returns charging interval from collection of charging intervals.
     * If charging intervals quantity is more than one it means that
     * there are should be day and week intervals and we have to return day interval.
     *
     * @param Collection|EquipmentCategoryChargingInterval[] $intervals
     *
     * @return null|EquipmentCategoryChargingInterval
     */
    public static function getCommonInterval(Collection $intervals): ?EquipmentCategoryChargingInterval
    {
        if ($intervals->count() > 1) {
            $intervals = $intervals->where('charging_interval', EquipmentCategoryChargingIntervals::DAY);
        }

        return $intervals->first();
    }
}
