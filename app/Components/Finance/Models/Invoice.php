<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Enums\InvoiceVirtualStatuses;
use App\Components\Finance\Events\InvoiceUpdated;
use App\Components\Finance\Interfaces\FinancialEntityStatusInterface;
use App\Components\Finance\InvoicesIndexConfigurator;
use App\Components\Finance\Resources\InvoicesListResource;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Tags\Models\Tag;
use App\Helpers\Decimal;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class Invoice
 *
 * @property string                                            $recipient_address
 * @property string                                            $recipient_name
 * @property Carbon                                            $due_at
 * @property Carbon|null                                       $locked_at
 * @property null|string                                       $reference
 * @property null|float                                        $total_receivables
 * @property null|float                                        $total_amount
 * @property null|float                                        $total_amount_paid
 * @property Carbon                                            $created_at
 * @property-read InvoiceStatus|FinancialEntityStatusInterface $latestStatus
 * @property-read InvoiceStatus[]                              $statuses
 * @property-read Collection|InvoiceApproveRequest[]           $approveRequests
 * @property-read Collection|Payment[]                         $payments
 * @property-read Collection|Transaction[]                     $transactions
 * @property-read Collection|Note[]                            $notes
 * @property-read Collection|Tag[]                             $tags
 *
 * @method static Builder|Invoice whereAccountingOrganizationId($value)
 * @method static Builder|Invoice whereCreatedAt($value)
 * @method static Builder|Invoice whereDocumentId($value)
 * @method static Builder|Invoice whereDueAt($value)
 * @method static Builder|Invoice whereId($value)
 * @method static Builder|Invoice whereJobId($value)
 * @method static Builder|Invoice whereLocationId($value)
 * @method static Builder|Invoice wherePaymentTermsDays($value)
 * @method static Builder|Invoice whereRecipientAddress($value)
 * @method static Builder|Invoice whereRecipientContactId($value)
 * @method static Builder|Invoice whereRecipientName($value)
 * @method static Builder|Invoice whereType($value)
 *
 * @OA\Schema(
 *     required={
 *          "location_id",
 *          "accounting_organization_id",
 *          "recipient_contact_id",
 *          "recipient_address",
 *          "recipient_name",
 *          "due_at",
 *     },
 * )
 *
 * @mixin \Eloquent
 * @package App\Components\Finance\Models
 */
class Invoice extends FinancialEntity
{
    use ApiRequestFillable, Searchable;

    public const UPDATED_AT = null;

    protected $dispatchesEvents = [
        'updated' => InvoiceUpdated::class,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date'       => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'locked_at'  => 'datetime:Y-m-d\TH:i:s\Z',
        'due_at'     => 'datetime:Y-m-d\TH:i:s\Z',
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
        'due_at',
    ];

    protected $indexConfigurator = InvoicesIndexConfigurator::class;

    /**
     * @OA\Property(
     *    property="id",
     *    description="Model identifier",
     *    type="integer",
     *    example=1
     * ),
     * @OA\Property(
     *    property="location_id",
     *    description="Location identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="job_id",
     *    description="Job identifier",
     *    type="integer",
     *    example=1,
     *    nullable=true,
     * ),
     * @OA\Property(
     *    property="accounting_organization_id",
     *    description="Accounting organization identifier",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="recipient_contact_id",
     *    description="Identifier of contact which is recipeint of invoice",
     *    type="integer",
     *    example=1,
     * ),
     * @OA\Property(
     *    property="recipient_address",
     *    description="Recipient address",
     *    type="string",
     *    example="300 Collins Street, Brisbane QLD 8000",
     * ),
     * @OA\Property(
     *    property="recipient_name",
     *    description="Recipient name",
     *    type="string",
     *    example="Joshua Brown",
     * ),
     * @OA\Property(
     *     property="due_at",
     *     description="Due at",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *     property="locked_at",
     *     description="Lock date means that invoice was approved or requested to approve or date of invoice less or
    equal lock date of the month",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="reference",
     *     description="Reference",
     *     type="string",
     *     example="Some reference",
     *     nullable=true
     * ),
     */

    /**
     * {@inheritdoc}
     */
    public function canBeApproved(): bool
    {
        return true;
    }

    /**
     * Returns the paid value for this invoice.
     *
     * @return float
     */
    public function getTotalPaid(): float
    {
        $totalPaid = 0;
        foreach ($this->payments as $payment) {
            /**@var \App\Components\Finance\Models\Payment $payment */
            $totalPaid += $payment->pivot->amount;
        }

        return $totalPaid;
    }

    /**
     * @inheritDoc
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * Indicates that invoice was paid in full before.
     *
     * @return bool
     */
    public function isPaidInFull(): bool
    {
        return Decimal::areEquals($this->getTotalAmount(), $this->getTotalPaid());
    }

    /**
     * @return float
     */
    public function getAmountDue(): float
    {
        return $this->getTotalAmount() - $this->getTotalPaid();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approveRequests(): HasMany
    {
        return $this->hasMany(InvoiceApproveRequest::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)
            ->orderBy('position')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(InvoiceStatus::class);
    }

    /**
     * Latest (or current) invoice status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this->hasOne(InvoiceStatus::class, 'invoice_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Returns current status name of the invoice.
     *
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->latestStatus()
            ->value('status');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payments(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Payment::class,
                'invoice_payment',
                'invoice_id',
                'payment_id'
            )
            ->withPivot(['amount', 'is_fp']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(
            Note::class,
            'invoice_note',
            'invoice_id',
            'note_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'invoice_tag',
            'invoice_id',
            'tag_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(
            Transaction::class,
            'invoice_transaction',
            'invoice_id',
            'transaction_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(
            UserNotification::class,
            'invoice_user_notification',
            'invoice_id',
            'user_notification_id'
        );
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $result['id']             = $this->id;
        $result['job_id']         = $this->job_id;
        $result['location_id']    = $this->location_id;
        $result['virtual_status'] = $this->getVirtualStatus();
        $result['status']         = $this->getLatestStatus()->getStatus();

        $result['data'] = InvoicesListResource::make($this);

        return $result;
    }

    /**
     * Checks whether if invoice payment overdue.
     *
     * @return bool
     */
    public function isOverDue(): bool
    {
        return Carbon::now() > $this->due_at;
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
            if (false === Decimal::isZero($this->getAmountDue())) {
                return $this->isOverDue() ? InvoiceVirtualStatuses::OVERDUE : InvoiceVirtualStatuses::UNPAID;
            }

            return InvoiceVirtualStatuses::PAID;
        }

        if ($this->hasApproveRequests()) {
            return InvoiceVirtualStatuses::PENDING_APPROVAL;
        }

        return InvoiceVirtualStatuses::DRAFT;
    }

    /**
     * Setter for due_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return \App\Components\Finance\Models\Invoice
     * @throws \Throwable
     */
    public function setDueAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('due_at', $datetime);
    }

    /**
     * Setter for locked_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return \App\Components\Finance\Models\Invoice
     * @throws \Throwable
     */
    public function seLockedAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('locked_at', $datetime);
    }
}
