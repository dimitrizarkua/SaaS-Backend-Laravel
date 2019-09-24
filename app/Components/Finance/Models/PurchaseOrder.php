<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\PurchaseOrderVirtualStatuses;
use App\Components\Finance\PurchaseOrderIndexConfigurator;
use App\Components\Finance\Resources\PurchaseOrderListResource;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Tags\Models\Tag;
use App\Helpers\Decimal;
use App\Models\ApiRequestFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class PurchaseOrder
 *
 * @package App\Components\Finance\Models
 *
 * @property string                                        $reference
 * @property float                                         $total_amount
 *
 * @property-read PurchaseOrderStatus                      $latestStatus
 * @property-read Collection|Tag[]                         $tags
 * @property-read Collection|Note[]                        $notes
 * @property-read Collection|UserNotification[]            $notifications
 * @property-read Collection|PurchaseOrderStatus[]         $statuses
 * @property-read Collection|PurchaseOrderApproveRequest[] $approveRequests
 *
 * @method static Builder|PurchaseOrder whereId($value)
 * @method static Builder|PurchaseOrder whereLocationId($value)
 * @method static Builder|PurchaseOrder whereAccountingOrganizationId($value)
 * @method static Builder|PurchaseOrder whereRecipientContactId($value)
 * @method static Builder|PurchaseOrder whereJobId($value)
 * @method static Builder|PurchaseOrder whereDocumentId($value)
 * @method static Builder|PurchaseOrder whereCreatedAt($value)
 * @method static Builder|PurchaseOrder whereLockedAt($value)
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     required={"id","location_id","accounting_organization_id", "recipient_contact_id", "date", "created_at"}
 * )
 */
class PurchaseOrder extends FinancialEntity
{
    use ApiRequestFillable, Searchable;

    /**
     * @OA\Property(
     *    property="id",
     *    description="Purchase order identifier",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="location_id",
     *    description="Identifier of location",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="accounting_organization_id",
     *    description="Identifier of accounting organization",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="recipient_contact_id",
     *    description="Identifier of recipient contact",
     *    type="integer",
     *    example="1"
     * ),
     * @OA\Property(
     *    property="job_id",
     *    description="Identifier of job",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="document_id",
     *    description="Identifier of document",
     *    type="integer",
     *    nullable=true,
     *    example="1"
     * ),
     * @OA\Property(
     *    property="date",
     *    description="Date",
     *    type="string",
     *    format="date",
     *    example="2018-11-10"
     * ),
     * @OA\Property(
     *    property="created_at",
     *    type="string",
     *    format="date-time"
     * ),
     * @OA\Property(
     *    property="locked_at",
     *    type="string",
     *    format="date-time",
     *    nullable=true
     * ),
     * @OA\Property(
     *     property="reference",
     *     description="Reference",
     *     type="string",
     *     example="Some reference",
     *     nullable=true
     * ),
     */

    public const UPDATED_AT = null;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date'       => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'locked_at'  => 'datetime:Y-m-d\TH:i:s\Z',
        'unit_cost'  => 'float',
        'markup'     => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date',
        'created_at',
        'locked_at',
    ];

    /**
     * Elasticsearch index.
     */
    protected $indexConfigurator = PurchaseOrderIndexConfigurator::class;

    /**
     * {@inheritdoc}
     */
    public function canBeApproved(): bool
    {
        return !Decimal::isZero($this->getTotalAmount());
    }

    /**
     * Relationship with purchase_order_tag table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'purchase_order_tag',
            'purchase_order_id',
            'tag_id'
        );
    }

    /**
     * Relationship with note_purchase_order table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(
            Note::class,
            'note_purchase_order',
            'purchase_order_id',
            'note_id'
        );
    }

    /**
     * Relationship with purchase_order_user_notification table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(
            UserNotification::class,
            'purchase_order_user_notification',
            'purchase_order_id',
            'user_notification_id'
        );
    }

    /**
     * Relationship with purchase_order_items table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)
            ->orderBy('position')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Relationship with purchase_order_statuses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(PurchaseOrderStatus::class, 'purchase_order_id');
    }

    /**
     * All approve requests of this purchase order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approveRequests(): HasMany
    {
        return $this->hasMany(PurchaseOrderApproveRequest::class, 'purchase_order_id');
    }

    /**
     * Indicates whether purchase order is approved or not.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        $status = $this->getLatestStatus()->getStatus();

        return $this->isLocked() && $status === FinancialEntityStatuses::APPROVED;
    }

    /**
     * Latest (or in other words, current) purchase order status.
     *
     * @return HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this->hasOne(PurchaseOrderStatus::class, 'purchase_order_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $result['id']             = $this->id;
        $result['location_id']    = $this->location_id;
        $result['virtual_status'] = $this->getVirtualStatus();
        $result['status']         = $this->getLatestStatus()->getStatus();

        $result['data'] = PurchaseOrderListResource::make($this);

        return $result;
    }

    /**
     * Returns virtual status.
     *
     * @return string
     */
    public function getVirtualStatus(): string
    {
        $latestStatus = $this->getLatestStatus()
            ->getStatus();

        if ($latestStatus === FinancialEntityStatuses::APPROVED) {
            return PurchaseOrderVirtualStatuses::APPROVED;
        }

        if ($this->hasApproveRequests()) {
            return PurchaseOrderVirtualStatuses::PENDING_APPROVAL;
        }

        return PurchaseOrderVirtualStatuses::DRAFT;
    }
}
