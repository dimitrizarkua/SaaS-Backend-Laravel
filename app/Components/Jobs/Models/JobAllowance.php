<?php

namespace App\Components\Jobs\Models;

use App\Components\UsageAndActuals\Models\AllowanceType;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class JobAllowance
 *
 * @property int                $id
 * @property int                $job_id
 * @property int                $user_id
 * @property int                $creator_id
 * @property int                $allowance_type_id
 * @property Carbon             $date_given
 * @property float              $charge_rate_per_interval
 * @property integer            $amount
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 * @property Carbon|null        $approved_at
 * @property int|null           $approver_id
 *
 * @property-read Job           $job
 * @property-read User          $user
 * @property-read User          $creator
 * @property-read AllowanceType $allowanceType
 * @property-read User          $approver
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "id",
 *         "job_id",
 *         "user_id",
 *         "creator_id",
 *         "allowance_type_id",
 *         "date_given",
 *         "charge_rate_per_interval",
 *         "amount",
 *         "created_at",
 *         "updated_at",
 *     }
 * )
 *
 * @package App\Components\Jobs\Models
 */
class JobAllowance extends Model
{
    use ApiRequestFillable, DateTimeFillable;
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
     *    property="user_id",
     *    description="Payee identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="creator_id",
     *    description="Creator identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="allowance_type_id",
     *    description="Allowance type identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(property="date_given", type="string", format="date-time"),
     * @OA\Property(
     *    property="charge_rate_per_interval",
     *    description="Charge rate per interval",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="amount",
     *    description="Amount",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(property="approved_at", type="string", format="date-time", nullable=true),
     * @OA\Property(
     *    property="approver_id",
     *    description="Approver identifier",
     *    type="integer",
     *    nullable=true,
     *    example=1,
     * ),
     */

    protected $table = 'job_allowances';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'approved_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_given'               => 'datetime:Y-m-d',
        'created_at'               => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'               => 'datetime:Y-m-d\TH:i:s\Z',
        'approved_at'              => 'datetime:Y-m-d\TH:i:s\Z',
        'charge_rate_per_interval' => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_given',
        'created_at',
        'updated_at',
        'approved_at',
    ];

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
     * Relationship with users table. Payee.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with users table. Creator.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Relationship with users table. Approver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Relationship with allowance_types table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceType::class, 'allowance_type_id');
    }

    /**
     * Setter for date_given attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setDateGivenAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('date_given', $datetime);
    }

    /**
     * Setter for approved_at attribute.
     *
     * @param string|Carbon $datetime
     *
     * @return self
     *
     * @throws \Throwable
     */
    public function setApprovedAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('approved_at', $datetime);
    }
}
