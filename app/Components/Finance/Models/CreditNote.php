<?php

namespace App\Components\Finance\Models;

use App\Components\Finance\CreditNotesIndexConfigurator;
use App\Components\Finance\Enums\CreditNoteVirtualStatuses;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Resources\CreditNoteListResource;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Tags\Models\Tag;
use App\Helpers\Decimal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use ScoutElastic\Searchable;

/**
 * Class CreditNote
 *
 * @property int|null                                   $payment_id
 * @property float|null                                 $total_amount
 * @property-read Payment                               $payment
 * @property-read Collection|CreditNoteStatus[]         $statuses
 * @property-read CreditNoteStatus                      $latestStatus
 * @property-read Collection|CreditNoteApproveRequest[] $approveRequests
 * @property-read Collection|Tag[]                      $tags
 * @property-read Collection|Note[]                     $notes
 * @property-read Collection|Transaction[]              $transactions
 * @property-read Collection|UserNotification[]         $userNotifications
 *
 * @OA\Schema(
 *     required={"id","location_id","accounting_organization_id","recipient_contact_id","date"}
 * )
 */
class CreditNote extends FinancialEntity
{
    use Searchable;

    /**
     * @OA\Property(
     *      property="location_id",
     *      description="Identifier of location",
     *      type="integer",
     *      example=1
     * ),
     * @OA\Property(
     *      property="accounting_organization_id",
     *      description="Identifier of accounting organization",
     *      type="integer",
     *      example=1
     * ),
     * @OA\Property(
     *      property="recipient_contact_id",
     *      description="Identifier of recipient's contact",
     *      type="integer",
     *      example=1
     * ),
     * @OA\Property(
     *      property="date",
     *      description="Execution start date",
     *      type="string",
     *      format="date",
     *      example="2018-11-10"
     * ),
     * @OA\Property(
     *      property="job_id",
     *      description="Identifier of job",
     *      type="integer",
     *      nullable=true,
     *      example=1
     * ),
     * @OA\Property(
     *      property="document_id",
     *      description="Identifier of PDF document",
     *      type="integer",
     *      nullable=true,
     *      example=1
     * ),
     * @OA\Property(
     *      property="payment_id",
     *      description="Identifier of payment",
     *      type="integer",
     *      nullable=true,
     *      example=1
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     * @OA\Property(
     *     property="locked_at",
     *     type="string",
     *     nullable=true,
     *     format="date-time",
     *     nullable=true,
     * ),
     */

    protected $indexConfigurator = CreditNotesIndexConfigurator::class;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date'       => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'locked_at'  => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'locked_at',
    ];

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

        $result['data'] = CreditNoteListResource::make($this);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function canBeApproved(): bool
    {
        return !Decimal::isZero($this->getTotalAmount());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Credit note statuses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this
            ->hasMany(CreditNoteStatus::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Latest (or current) credit note status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this
            ->hasOne(CreditNoteStatus::class, 'credit_note_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Returns current status name of the credit note.
     *
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->latestStatus()
            ->value('status');
    }

    /**
     * @inheritDoc
     */
    public function items(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class)
            ->orderBy('position')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Returns list of credit note approve requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approveRequests(): HasMany
    {
        return $this
            ->hasMany(CreditNoteApproveRequest::class)
            ->orderBy('approver_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'credit_note_tag');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'credit_note_note');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'credit_note_transaction');
    }

    /**
     * Relationship with credit_note_user_notification table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(
            UserNotification::class,
            'credit_note_user_notification',
            'credit_note_id',
            'user_notification_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userNotifications(): BelongsToMany
    {
        return $this->belongsToMany(UserNotification::class, 'credit_note_user_notification');
    }

    /**
     * @inheritDoc
     */
    public function getVirtualStatus(): string
    {
        $latestStatus = $this->getLatestStatus()
            ->getStatus();

        if ($latestStatus === FinancialEntityStatuses::APPROVED) {
            return CreditNoteVirtualStatuses::APPROVED;
        }

        if ($this->hasApproveRequests()) {
            return CreditNoteVirtualStatuses::PENDING_APPROVAL;
        }

        return CreditNoteVirtualStatuses::DRAFT;
    }
}
