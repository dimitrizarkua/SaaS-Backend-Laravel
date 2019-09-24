<?php

namespace App\Components\Finance\Models;

use App\Components\Contacts\Models\Contact;
use App\Components\Documents\Models\Document;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\FinanceEntityIdOrJobSearchRule;
use App\Components\Finance\FinanceEntitySearchRule;
use App\Components\Finance\Interfaces\FinancialEntityStatusInterface;
use App\Components\Jobs\Models\Job;
use App\Components\Locations\Models\Location;
use App\Models\DateTimeFillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class FinancialEntity
 *
 * @property int                                   $id
 * @property int                                   $location_id
 * @property int                                   $accounting_organization_id
 * @property int                                   $recipient_contact_id
 * @property int|null                              $job_id
 * @property int|null                              $document_id
 * @property Carbon                                $locked_at
 * @property Carbon                                $date
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 * @property string                                $recipient_address
 * @property string                                $recipient_name
 *
 * @property-read Collection|FinancialEntityItem[] $items
 * @property-read AccountingOrganization           $accountingOrganization
 * @property-read Document|null                    $document
 * @property-read Job|null                         $job
 * @property-read Location                         $location
 * @property-read Contact                          $recipientContact
 * @property-read Collection                       $approveRequests
 *
 * @method static Builder|static shouldBeLocked()
 *
 * @package App\Components\Finance\Models
 * @mixin \Eloquent
 * @mixin \ScoutElastic\Searchable
 */
abstract class FinancialEntity extends Model
{
    use DateTimeFillable;

    public const SEARCH_LIMIT = 10;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'created_at',
    ];

    protected $searchRules = [
        FinanceEntitySearchRule::class,
    ];

    /**
     * Elasticsearch mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'id'             => [
                'type' => 'text',
            ],
            'job_id'         => [
                'type' => 'text',
            ],
            'location_id'    => [
                'type' => 'long',
            ],
            'virtual_status' => [
                'type' => 'text',
            ],
            'status'         => [
                'type' => 'text',
            ],
            'data'           => [
                'enabled' => false,
            ],
        ],
    ];

    /**
     * Returns can entity approved with zero balance
     *
     * @return bool
     */
    abstract public function canBeApproved(): bool;

    /**
     * Defines relationship with locations table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Defines relationship with accounting_organization table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingOrganization(): BelongsTo
    {
        return $this->belongsTo(AccountingOrganization::class);
    }

    /**
     * Defines relationship with contacts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipientContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'recipient_contact_id');
    }

    /**
     * Defines relationship with jobs table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Defines relationship with documents table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Scope a query to include purchase orders that should be locked.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldBeLocked(Builder $query): Builder
    {
        $accountingOrganizationsIds = AccountingOrganization::lockDayOfMonthIsToday()
            ->pluck('id');

        return $query->whereNull('locked_at')
            ->whereIn('accounting_organization_id', $accountingOrganizationsIds)
            ->whereDate('date', '<=', Carbon::now());
    }

    /**
     * Allows to lock up an entity.
     *
     * @return void
     */
    public function lockUp(): void
    {
        if (null === $this->locked_at) {
            $this->locked_at = Carbon::now();
            $this->save();
        }
    }

    /**
     * Checks whether the entity can be modified.
     *
     * @return bool
     */
    public function canBeModified(): bool
    {
        return false === $this->isLocked() && true === $this->isDraft();
    }

    /**
     * Returns latest status of entity.
     *
     * @return \App\Components\Finance\Interfaces\FinancialEntityStatusInterface
     */
    public function getLatestStatus(): FinancialEntityStatusInterface
    {
        return $this->latestStatus;
    }

    /**
     * Checks whether is entity locked.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool)$this->locked_at;
    }

    /**
     * Checks whether is entity has draft status.
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        $latestStatus = $this->getLatestStatus();
        if (null === $latestStatus) {
            return false;
        }

        return $latestStatus->getStatus() === FinancialEntityStatuses::DRAFT;
    }

    /**
     * Checks whether is entity has approved status.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        $latestStatus = $this->getLatestStatus();
        if (null === $latestStatus) {
            return false;
        }

        return $latestStatus->getStatus() === FinancialEntityStatuses::APPROVED;
    }

    /**
     * Returns the date for which entity was created.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * Returns accounting organization.
     *
     * @return AccountingOrganization
     */
    public function getAccountingOrganization(): AccountingOrganization
    {
        return $this->accountingOrganization;
    }

    /**
     * Returns location id related to the entity.
     *
     * @return int
     */
    public function getLocationId(): int
    {
        return $this->location_id;
    }

    /**
     * Returns document id linked with the entity.
     *
     * @return null|int
     */
    public function getDocumentId(): ?int
    {
        return $this->document_id;
    }

    /**
     * Returns created_at attribute of entity.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    /**
     * Checks whether is invoice has approve requests.
     *
     * @return bool
     */
    public function hasApproveRequests(): bool
    {
        return 0 !== $this->approveRequests->count();
    }

    /**
     * Checks whether is invoice can be deleted.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return true === $this->canBeModified() && false === $this->hasApproveRequests();
    }

    /**
     * Returns formatted id for finance entities.
     *
     * @return string
     */
    public function getFormattedId(): string
    {
        $code = $this->location->code;

        return null !== $code
            ? '#' . $this->id . '-' . $code
            : '#' . $this->id;
    }

    /**
     * Allows to search entities for numbers (ids).
     *
     * @param array       $options       Array that should contain number of entity.
     * @param array       $locationIds   Array with authorized user's locations ids.
     * @param string      $virtualStatus Virtual status for additional filtration.
     * @param string|null $status        Status for additional filtration.
     *
     * @return Collection
     */
    public static function searchForNumbers(
        array $options,
        array $locationIds = [],
        string $virtualStatus = null,
        string $status = null
    ): Collection {
        $query = static::search($options)
            ->whereIn('location_id', $locationIds);

        if (null !== $virtualStatus) {
            $query->where('virtual_status', $virtualStatus);
        }

        if (null !== $status) {
            $query->where('status', $status);
        }

        $raw = $query->take(self::SEARCH_LIMIT)
            ->raw();

        return collect(mapElasticResults($raw))->pluck('data');
    }

    /**
     * Allows to search entities for numbers of entities or jobs (ids).
     *
     * @param array $numbers
     *
     * @return \Illuminate\Support\Collection
     */
    public static function searchForNumbersOfEntitiesOrJobs(array $numbers)
    {
        $raw = static::search($numbers)
            ->rule(FinanceEntityIdOrJobSearchRule::class)
            ->take(self::SEARCH_LIMIT)
            ->raw();

        return collect(mapElasticResults($raw))->pluck('data');
    }

    /**
     * Returns collection of Financial Entities filtered by statuses with optimized query.
     *
     * @param array $ids            Entity identifiers.
     * @param array $statusList     List of statuses to filter.
     * @param array $withRelations  List of relations to pass in laravel `with` method.
     * @param array $selectedFields Fields for select.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getCollectionByStatuses(
        array $ids,
        array $statusList,
        array $withRelations = [],
        array $selectedFields = []
    ): Collection {
        $obj = new static();

        $table = str_singular($obj->getTable());

        $statusQuery = DB::query()->select(["{$table}_id"])
            ->from("{$table}_statuses")
            ->whereIn("{$table}_id", $ids)
            ->whereIn('status', $statusList);

        $fieldsByDefault = ['id'];

        $entities = $obj->newQuery()
            ->with($withRelations)
            ->select(["{$table}_ids.*"])
            ->fromSub(
                DB::query()
                    ->select(array_merge($fieldsByDefault, $selectedFields))
                    ->from("{$table}s")
                    ->whereIn('id', $ids),
                "{$table}_ids"
            )
            ->joinSub(
                $statusQuery,
                "{$table}_statuses_records",
                "{$table}_ids.id",
                '=',
                "{$table}_statuses_records.{$table}_id"
            );

        return $entities->get();
    }

    /**
     * Defines relationship with notes table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    abstract public function notes(): BelongsToMany;

    /**
     * Defines relationship with tags table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    abstract public function tags(): BelongsToMany;

    /**
     * Defines relationship with statuses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function statuses(): HasMany;

    /**
     * Defines relationship with *_approve_request table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function approveRequests(): HasMany;

    /**
     * Returns virtual status.
     *
     * @return string
     */
    abstract public function getVirtualStatus(): string;

    /**
     * Latest status.
     *
     * @return HasOne
     */
    abstract public function latestStatus(): HasOne;

    /**
     * Returns related items.
     *
     * @return HasMany
     */
    abstract public function items(): HasMany;

    /**
     * Returns total amount of the entity.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->getTaxesAmount() + $this->getSubTotalAmount();
    }

    /**
     * Returns sub total amount.
     *
     * @return float
     */
    public function getSubTotalAmount(): float
    {
        $subTotalAmount = $this->items
            ->reduce(function (float $total, FinancialEntityItem $item) {
                return $total + $item->getSubTotal();
            }, 0);

        return $subTotalAmount;
    }

    /**
     * Returns taxes amount.
     *
     * @return float
     */
    public function getTaxesAmount(): float
    {
        $taxesAmount = $this->items
            ->reduce(function (float $total, FinancialEntityItem $item) {
                return $total + $item->getItemTax();
            }, 0);

        return $taxesAmount;
    }

    /**
     * Setter for date attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return \App\Components\Finance\Models\Invoice
     * @throws \Throwable
     */
    public function setDateAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('date', $datetime);
    }
}
