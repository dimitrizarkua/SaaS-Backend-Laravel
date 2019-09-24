<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Interfaces\FinancialEntityStatusInterface;
use App\Models\ApiRequestFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderStatus
 *
 * @package App\Components\Finance\Models
 *
 * @property int                $id
 * @property int                $purchase_order_id
 * @property int                $user_id
 * @property string             $status
 * @property string             $created_at
 * @property-read PurchaseOrder $purchaseOrderId
 * @property-read User          $user
 *
 * @method static Builder|PurchaseOrder newModelQuery()
 * @method static Builder|PurchaseOrder newQuery()
 * @method static Builder|PurchaseOrder query()
 * @method static Builder|PurchaseOrder whereId($value)
 * @method static Builder|PurchaseOrder wherePurchaseOrderId($value)
 * @method static Builder|PurchaseOrder whereUserId($value)
 * @method static Builder|PurchaseOrder whereStatus($value)
 * @method static Builder|PurchaseOrder whereCreatedAt($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id", "purchase_order_id","user_id", "status", "created_at"}
 * )
 */
class PurchaseOrderStatus extends Model implements FinancialEntityStatusInterface
{
    use ApiRequestFillable;

    /**
     * The map of possible changes of statuses.
     *
     * @var array
     */
    private $statusesMap = [
        FinancialEntityStatuses::DRAFT => [
            FinancialEntityStatuses::APPROVED,
        ],
    ];

    /**
     * @OA\Property(
     *    property="id",
     *    description="Purchase order status identifier",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="purchase_order_id",
     *    description="Identifier of purchase order",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="user_id",
     *    description="Identifier of user",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="status",
     *    description="Purchase order status",
     *    type="string",
     *    example="Approved"
     * ),
     * @OA\Property(
     *    property="created_at",
     *    type="string",
     *    format="date-time"
     * ),
     */

    public const UPDATED_AT = null;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
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
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_order_statuses';

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
     * Relationship with purchase_orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * User who set status to purchase order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @inheritDoc
     */
    public function canBeChangedTo(string $newStatus): bool
    {
        if ($newStatus === $this->status) {
            return false;
        }

        return array_key_exists($this->status, $this->statusesMap)
            && in_array($newStatus, $this->statusesMap[$this->status], true);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
