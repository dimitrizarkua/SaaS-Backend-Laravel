<?php

namespace App\Components\Jobs\Models;

use App\Components\Documents\Models\Document;
use App\Components\Finance\Models\InvoiceItem;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class JobReimbursement
 *
 * @property int              $id
 * @property int              $job_id
 * @property int              $user_id
 * @property int              $creator_id
 * @property Carbon           $date_of_expense
 * @property int              $document_id
 * @property string           $description
 * @property float            $total_amount
 * @property boolean          $is_chargeable
 * @property int|null         $invoice_item_id
 * @property Carbon           $created_at
 * @property Carbon           $updated_at
 * @property Carbon|null      $approved_at
 * @property int|null         $approver_id
 *
 * @property-read Job         $job
 * @property-read User        $user
 * @property-read User        $creator
 * @property-read Document    $document
 * @property-read InvoiceItem $invoiceItem
 * @property-read User        $approver
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
 *         "date_of_expense",
 *         "document_id",
 *         "description",
 *         "total_amount",
 *         "is_chargeable",
 *         "created_at",
 *         "updated_at",
 *     }
 * )
 *
 * @package App\Components\Jobs\Models
 */
class JobReimbursement extends Model
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
     * @OA\Property(property="date_of_expense", type="string", format="date"),
     * @OA\Property(
     *    property="document_id",
     *    description="Document identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="description",
     *    description="Description",
     *    type="string",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="total_amount",
     *    description="Total amount",
     *    type="number",
     *    format="float",
     *    example=12.3
     * ),
     * @OA\Property(
     *    property="is_chargeable",
     *    description="Is current reimbursment chargeable",
     *    type="boolean",
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
     * @OA\Property(property="approved_at", type="string", format="date-time", nullable=true),
     * @OA\Property(
     *    property="approver_id",
     *    description="Approver identifier",
     *    type="integer",
     *    nullable=true,
     *    example=1,
     * ),
     */

    protected $table = 'job_reimbursements';

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
        'date_of_expense' => 'datetime:Y-m-d',
        'created_at'      => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'      => 'datetime:Y-m-d\TH:i:s\Z',
        'approved_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'total_amount'    => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_of_expense',
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
     * Relationship with documents table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
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
