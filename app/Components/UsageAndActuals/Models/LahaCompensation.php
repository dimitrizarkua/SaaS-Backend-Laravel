<?php

namespace App\Components\UsageAndActuals\Models;

use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Locations\Models\Location;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class LahaCompensation
 *
 * @property int                                   $id
 * @property float                                 $rate_per_day
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 *
 * @property-read Collection|JobLahaCompensation[] $jobLahaCompensations
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
 *          "rate_per_day",
 *          "created_at",
 *          "updated_at",
 *     }
 * )
 *
 * @package App\Components\UsageAndActuals\Models
 */
class LahaCompensation extends Model
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
     *    property="rate_per_day",
     *    description="Charge rate per interval",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     */

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
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
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
     * Assigned location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Relationship with insurer_contracts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobLahaCompensations(): HasMany
    {
        return $this->hasMany(JobLahaCompensation::class);
    }
}
