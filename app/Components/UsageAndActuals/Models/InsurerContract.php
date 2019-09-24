<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Contacts\Models\Contact;
use App\Components\Jobs\Models\Job;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use OpenApi\Annotations as OA;

/**
 * Class InsurerContract
 *
 * @property int                                 $id
 * @property int                                 $contact_id
 * @property string                              $contract_number
 * @property string|null                         $description
 * @property Carbon                              $created_at
 * @property Carbon                              $effect_date
 * @property Carbon|null                         $termination_date
 * @property-read Contact                        $contact
 * @property-read Collection|Job[]               $jobs
 * @property-read Collection|EquipmentCategory[] $equipmentCategories
 *
 * @method static Builder|InsurerContract newModelQuery()
 * @method static Builder|InsurerContract newQuery()
 * @method static Builder|InsurerContract query()
 * @method static Builder|InsurerContract whereCreatedAt($value)
 * @method static Builder|InsurerContract whereId($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "contact_id",
 *          "contract_number",
 *          "effect_date",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class InsurerContract extends Model
{
    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="contact_id",
     *    description="Identifier of contact",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="contract_number",
     *    description="Number of insurer contract",
     *    type="string",
     * ),
     * @OA\Property(
     *    property="description",
     *    description="Insurer contract description",
     *    type="string",
     *    example="Some text about contract.",
     *    nullable=true,
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(
     *     property="effect_date",
     *     description="Date when contract activated",
     *     type="string",
     *     format="date",
     *     example="2018-11-10"
     * ),
     * @OA\Property(
     *     property="termination_date",
     *     description="Date when contract terminated",
     *     type="string",
     *     format="date",
     *     example="2018-11-10",
     *     nullable=true,
     * ),
     */

    const UPDATED_AT = null;

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'       => 'datetime:Y-m-d\TH:i:s\Z',
        'effect_date'      => 'datetime:Y-m-d',
        'termination_date' => 'datetime:Y-m-d',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'effect_date',
        'termination_date',
    ];

    /**
     * The person who works under this contract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Jobs performed under this contract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Equipment categories which the contract are used.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function equipmentCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            EquipmentCategory::class,
            'equipment_category_insurer_contract',
            'insurer_contract_id',
            'equipment_category_id'
        );
    }

    /**
     * Returns is current contract active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        return $this->effect_date <= $currentDate
            && $this->termination_date >= $currentDate
            || is_null($this->termination_date);
    }

    /**
     * Returns is current contract terminated.
     *
     * @return bool
     */
    public function isTerminated(): bool
    {
        $currentDate = Carbon::now()->format('Y-m-d');

        return $this->termination_date ? $this->termination_date <= $currentDate : false;
    }
}
