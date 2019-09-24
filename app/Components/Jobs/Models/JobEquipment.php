<?php

namespace App\Components\Jobs\Models;

use App\Components\Finance\Models\InvoiceItem;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class JobEquipment
 *
 * @property int                                            $id
 * @property int                                            $job_id
 * @property int                                            $equipment_id
 * @property int                                            $creator_id
 * @property Carbon                                         $started_at
 * @property Carbon|null                                    $ended_at
 * @property string                                         $interval
 * @property int|null                                       $intervals_count
 * @property int|null                                       $intervals_count_override
 * @property float                                          $buy_cost_per_interval
 * @property int|null                                       $invoice_item_id
 * @property Carbon                                         $updated_at
 * @property Carbon                                         $created_at
 * @property Carbon|null                                    $deleted_at
 * @property-read Equipment                                 $equipment
 * @property-read Job                                       $job
 * @property-read User                                      $creator
 * @property-read InvoiceItem|null                          $invoiceItem
 * @property-read Collection|JobEquipmentChargingInterval[] $chargingIntervals
 *
 * @method static Builder|JobEquipment newModelQuery()
 * @method static Builder|JobEquipment newQuery()
 * @method static Builder|JobEquipment query()
 * @method static Builder|JobEquipment whereId($value)
 * @method static Builder|JobEquipment whereJobId($value)
 * @method static Builder|JobEquipment whereEquipmentId($value)
 * @method static Builder|JobEquipment whereCreatorId($value)
 * @method static Builder|JobEquipment whereStartedAt($value)
 * @method static Builder|JobEquipment whereEndedAt($value)
 * @method static Builder|JobEquipment whereInterval($value)
 * @method static Builder|JobEquipment whereIntervalsCount($value)
 * @method static Builder|JobEquipment whereIntervalsCountOverride($value)
 * @method static Builder|JobEquipment whereBuyCostPerInterval($value)
 * @method static Builder|JobEquipment whereInvoiceItemId($value)
 * @method static Builder|JobEquipment whereCreatedAt($value)
 * @method static Builder|JobEquipment whereUpdatedAt($value)
 * @method static Builder|JobEquipment whereDeletedAt($value)
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={
 *         "id",
 *         "job_id",
 *         "equipment_id",
 *         "creator_id",
 *         "started_at",
 *         "interval",
 *         "buy_cost_per_interval",
 *     }
 * )
 *
 * @package App\Components\Jobs\Models
 */
class JobEquipment extends Model
{
    use SoftDeletes, DateTimeFillable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Job equipment identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="job_id",
     *     description="Job identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="equipment_id",
     *     description="Equipment identifier",
     *     type="integer",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="creator_id",
     *     description="Identifier of user who created record",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="started_at",
     *     description="Started at datetime",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z",
     * ),
     * @OA\Property(
     *     property="ended_at",
     *     description="Ended at datetime",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="interval",
     *     description="Charging interval for job equipment",
     *     type="string",
     *     example="day",
     * ),
     * @OA\Property(
     *     property="intervals_count",
     *     description="Count of intervals",
     *     type="integer",
     *     example=3,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="intervals_count_override",
     *     description="Override count of intervals",
     *     type="integer",
     *     example=5,
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="buy_cost_per_interval",
     *     description="Buy cost per interval for job equipment",
     *     type="number",
     *     format="float",
     *     example=50.85,
     * ),
     * @OA\Property(
     *     property="invoice_item_id",
     *     description="Invoice item identifier",
     *     type="integer",
     *     example=1,
     *     nullable=true,
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     */

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_equipment';

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
        'started_at'            => 'datetime:Y-m-d\TH:i:s\Z',
        'ended_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'created_at'            => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'            => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'            => 'datetime:Y-m-d\TH:i:s\Z',
        'buy_cost_per_interval' => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'started_at',
        'ended_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Relationship with equipment table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Relationship with jobs table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    /**
     * Relationship with users table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship with invoice_items table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    /**
     * Charging intervals for specific job equipment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chargingIntervals(): HasMany
    {
        return $this->hasMany(JobEquipmentChargingInterval::class);
    }

    /**
     * Setter for started_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setStartedAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('started_at', $datetime);
    }

    /**
     * Setter for ended_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setEndedAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('ended_at', $datetime);
    }

    /**
     * Returns total charge (rate * intervals count).
     *
     * @return float
     */
    public function getTotalCharge(): float
    {
        $chargingInterval = $this->getDefaultChargingInterval();

        return null !== $chargingInterval
            ? $chargingInterval->charging_rate_per_interval * $this->intervals_count_override
            : 0;
    }

    /**
     * Returns default job equipment charging interval.
     *
     * @return JobEquipmentChargingInterval|null
     */
    public function getDefaultChargingInterval(): ?JobEquipmentChargingInterval
    {
        return $this->chargingIntervals
            ->where('charging_interval', $this->interval)
            ->first();
    }
}
