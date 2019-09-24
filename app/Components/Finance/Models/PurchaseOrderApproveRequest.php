<?php

namespace App\Components\Finance\Models;

use App\Models\ApiRequestFillable;
use App\Models\HasCompositePrimaryKey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderApproveRequest
 *
 * @package App\Components\Finance\Models
 *
 * @property int                $purchase_order_id
 * @property int                $requester_id
 * @property int                $approver_id
 * @property Carbon|null        $approved_at
 * @property-read PurchaseOrder $purchaseOrderId
 * @property-read User          $requester
 * @property-read User          $approver
 *
 * @method static Builder|PurchaseOrder newModelQuery()
 * @method static Builder|PurchaseOrder newQuery()
 * @method static Builder|PurchaseOrder query()
 * @method static Builder|PurchaseOrder wherePurchaseOrderId($value)
 * @method static Builder|PurchaseOrder whereRequesterId($value)
 * @method static Builder|PurchaseOrder whereApproverId($value)
 * @method static Builder|PurchaseOrder whereApprovedAt($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"purchase_order_id","requester_id", "approver_id"}
 * )
 */
class PurchaseOrderApproveRequest extends Model
{
    use ApiRequestFillable;

    /**
     * @OA\Property(
     *    property="purchase_order_id",
     *    description="Identifier of purchase order",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="requester_id",
     *    description="Identifier of requester user",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="approver_id",
     *    description="Identifier of approver user",
     *    type="integer",
     *    example="2"
     * ),
     * @OA\Property(
     *    property="approved_at",
     *    type="string",
     *    format="date",
     *    example="2018-11-10T09:10:11Z",
     *    nullable="true"
     * ),
     */

    use HasCompositePrimaryKey;

    /**
     * The primary key for the model.
     *
     * @var string
     */

    protected $primaryKey = [
        'purchase_order_id',
        'requester_id',
        'approver_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_order_approve_requests';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'approved_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'approved_at',
    ];

    /**
     * Relationship with purchase_orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * User who requested approve.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /**
     * User who approved purchase order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
