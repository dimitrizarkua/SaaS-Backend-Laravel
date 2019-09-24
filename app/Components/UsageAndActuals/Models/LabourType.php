<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Jobs\Models\JobLabour;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class LabourType
 *
 * @property int                               $id
 * @property string                            $name
 * @property float                             $first_tier_hourly_rate
 * @property float                             $second_tier_hourly_rate
 * @property float                             $third_tier_hourly_rate
 * @property float                             $fourth_tier_hourly_rate
 * @property Carbon                            $created_at
 *
 * @property-read Collection|JobLabour[]       $jobLabours
 * @property-read Collection|InsurerContract[] $insurerContracts
 *
 * @method static Builder|InsurerContract query()
 * @method static Builder|InsurerContract whereCreatedAt($value)
 * @method static Builder|InsurerContract whereId($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *          "id",
 *          "name",
 *          "first_tier_hourly_rate",
 *          "second_tier_hourly_rate",
 *          "third_tier_hourly_rate",
 *          "fourth_tier_hourly_rate",
 *          "created_at",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class LabourType extends Model
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
     *    description="Name of labour type",
     *    type="string",
     * ),
     * @OA\Property(
     *    property="first_tier_hourly_rate",
     *    description="First tier hourly rate",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="second_tier_hourly_rate",
     *    description="Second tier hourly rate",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="third_tier_hourly_rate",
     *    description="Third tier hourly rate",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="fourth_tier_hourly_rate",
     *    description="Fourth tier hourly rate",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
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
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
    ];

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
     * Relationship with job_labours table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobLabours(): HasMany
    {
        return $this->hasMany(JobLabour::class);
    }
}
