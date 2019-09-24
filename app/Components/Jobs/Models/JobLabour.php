<?php

namespace App\Components\Jobs\Models;

use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\UsageAndActuals\Models\Holiday;
use App\Components\UsageAndActuals\Models\InsurerContractLabourType;
use App\Components\UsageAndActuals\Models\LabourType;
use App\Helpers\DateHelper;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class JobLabour
 *
 * @property int              $id
 * @property int              $job_id
 * @property int              $labour_type_id
 * @property int              $worker_id
 * @property int              $creator_id
 * @property Carbon           $started_at
 * @property Carbon           $ended_at
 * @property Carbon           $started_at_override
 * @property Carbon           $ended_at_override
 * @property int|null         $break
 * @property float            $first_tier_hourly_rate
 * @property float            $second_tier_hourly_rate
 * @property float            $third_tier_hourly_rate
 * @property float            $fourth_tier_hourly_rate
 * @property int              $first_tier_time_amount
 * @property int              $second_tier_time_amount
 * @property int              $third_tier_time_amount
 * @property int              $fourth_tier_time_amount
 * @property float            $calculated_total_amount
 * @property int|null         $invoice_item_id
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property Carbon|null      $deleted_at
 *
 * @property-read Job         $job
 * @property-read LabourType  $labourType
 * @property-read User        $worker
 * @property-read User        $creator
 * @property-read InvoiceItem $invoiceItem
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "id",
 *         "job_id",
 *         "labour_type_id",
 *         "worker_id",
 *         "creator_id",
 *         "started_at",
 *         "ended_at",
 *         "started_at_override",
 *         "ended_at_override",
 *         "first_tier_hourly_rate",
 *         "second_tier_hourly_rate",
 *         "third_tier_hourly_rate",
 *         "fourth_tier_hourly_rate",
 *         "calculated_total_amount",
 *         "created_at",
 *         "updated_at",
 *     }
 * )
 *
 * @package App\Components\Jobs\Models
 */
class JobLabour extends Model
{
    use SoftDeletes, DateTimeFillable;

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="job_id",
     *    description="Job identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="labour_type_id",
     *    description="Labour type identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="worker_id",
     *    description="User-worker identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="creator_id",
     *    description="User-creator identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(property="started_at", type="string", format="date-time"),
     * @OA\Property(property="ended_at", type="string", format="date-time"),
     * @OA\Property(property="started_at_override", type="string", format="date-time"),
     * @OA\Property(property="ended_at_override", type="string", format="date-time"),
     * @OA\Property(property="break", type="integer", nullable=true),
     * @OA\Property(
     *    property="first_tier_hourly_rate",
     *    description="First tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="second_tier_hourly_rate",
     *    description="Second tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="third_tier_hourly_rate",
     *    description="Third tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="fourth_tier_hourly_rate",
     *    description="Fourth tier hourly rate",
     *    type="number",
     *    format="float",
     *    nullable=true,
     *    example=12.3,
     * ),
     * @OA\Property(
     *    property="first_tier_time_amount",
     *    description="Calculated time amount for first tier rate in minutes",
     *    type="integer",
     *    example=12
     * ),
     * @OA\Property(
     *    property="second_tier_time_amount",
     *    description="Calculated time amount for second tier rate in minutes",
     *    type="integer",
     *    example=12
     * ),
     * @OA\Property(
     *    property="third_tier_time_amount",
     *    description="Calculated time amount for third tier rate in minutes",
     *    type="integer",
     *    example=12
     * ),
     * @OA\Property(
     *    property="fourth_tier_time_amount",
     *    description="Calculated time amount for fourth tier rate in minutes",
     *    type="integer",
     *    example=12
     * ),
     * @OA\Property(
     *    property="calculated_total_amount",
     *    description="Calculated total amount based on insurer contract",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="invoice_item_id",
     *    description="Invoice item identifier",
     *    type="integer",
     *    nullable=true,
     *    example=1,
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
     */

    protected $table = 'job_labours';

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
        'created_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'started_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'ended_at'                => 'datetime:Y-m-d\TH:i:s\Z',
        'started_at_override'     => 'datetime:Y-m-d\TH:i:s\Z',
        'ended_at_override'       => 'datetime:Y-m-d\TH:i:s\Z',
        'first_tier_hourly_rate'  => 'float',
        'second_tier_hourly_rate' => 'float',
        'third_tier_hourly_rate'  => 'float',
        'fourth_tier_hourly_rate' => 'float',
        'calculated_total_amount' => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at'          => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'          => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'          => 'datetime:Y-m-d\TH:i:s\Z',
        'started_at'          => 'datetime:Y-m-d\TH:i:s\Z',
        'ended_at'            => 'datetime:Y-m-d\TH:i:s\Z',
        'started_at_override' => 'datetime:Y-m-d\TH:i:s\Z',
        'ended_at_override'   => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * Associated job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Relationship with labour_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function labourType(): BelongsTo
    {
        return $this->belongsTo(LabourType::class, 'labour_type_id');
    }

    /**
     * Relationship with users table. Worker receiving laha compensation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    /**
     * Relationship with users table. Creator of laha compensation.
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
     * Returns invoice associated with labour.
     *
     * @return \App\Components\Finance\Models\Invoice|null
     */
    public function getInvoice(): ?Invoice
    {
        return $this->invoice_item_id ? $this->invoiceItem->invoice : null;
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
     * Setter for started_at_override attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setStartedAtOverrideAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('started_at_override', $datetime);
    }

    /**
     * Setter for ended_at_override attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setEndedAtOverrideAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('ended_at_override', $datetime);
    }

    /**
     * Updates hourly_rate fields.
     *
     * @return self
     */
    public function updateHourlyRates(): self
    {
        /** @var InsurerContractLabourType $insurerContractLabourType */
        $insurerContractLabourType = InsurerContractLabourType::query()
            ->where('insurer_contract_id', $this->job->insurer_contract_id)
            ->where('labour_type_id', $this->labour_type_id)
            ->first();

        $laborType = $insurerContractLabourType
            ?: $this->labourType;

        $this->first_tier_hourly_rate  = $laborType->first_tier_hourly_rate;
        $this->second_tier_hourly_rate = $laborType->second_tier_hourly_rate;
        $this->third_tier_hourly_rate  = $laborType->third_tier_hourly_rate;
        $this->fourth_tier_hourly_rate = $laborType->fourth_tier_hourly_rate;

        return $this;
    }

    /**
     * Calculates total amount for current job labour based on insurer contract.
     *
     * @return float
     * @throws \Exception
     */
    public function calculateTotalAmount(): float
    {
        $amount                 = 0;
        $workHoursAmountByTiers = $this->calculateTimeIntervals();

        $amount += $workHoursAmountByTiers['firstTierAmount'] * $this->first_tier_hourly_rate / 60;
        $amount += $workHoursAmountByTiers['secondTierAmount'] * $this->second_tier_hourly_rate / 60;
        $amount += $workHoursAmountByTiers['thirdTierAmount'] * $this->third_tier_hourly_rate / 60;
        $amount += $workHoursAmountByTiers['fourthTierAmount'] * $this->fourth_tier_hourly_rate / 60;

        return $amount;
    }

    /**
     * @return array
     */
    public function calculateTimeIntervals(): array
    {
        $startTime        = clone $this->started_at_override;
        $endedAtWithBreak = clone $this->ended_at_override;
        if ($this->break) {
            $endedAtWithBreak->subMinutes($this->break);
        }

        return DateHelper::calculateWorkTimeByInterval(
            $startTime,
            $endedAtWithBreak,
            Holiday::all()
        );
    }
}
