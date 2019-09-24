<?php

namespace App\Components\Contacts\Models;

use App\Components\Addresses\Models\Address;
use App\Components\Contacts\Exceptions\InvalidArgumentException;
use App\Components\Contacts\Models\Enums\AddressContactTypes;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobContactAssignmentType;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Photos\Models\Photo;
use App\Components\Tags\Models\Tag;
use App\Components\Contacts\ContactsIndexConfigurator;
use App\Components\UsageAndActuals\Models\InsurerContract;
use App\Models\ApiRequestFillable;
use App\Models\HasLatestStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Builder as ScoutBuilder;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class Contact
 *
 * @package App\Components\Contacts\Models
 * @mixin \Eloquent
 *
 * @property int                                        $id
 * @property string                                     $contact_type
 * @property int                                        $contact_category_id
 * @property string|null                                $avatar_url
 * @property int|null                                   $avatar_photos_id
 * @property string|null                                $email
 * @property string|null                                $business_phone
 * @property \Illuminate\Support\Carbon                 $last_active_at
 * @property \Illuminate\Support\Carbon                 $created_at
 * @property \Illuminate\Support\Carbon                 $updated_at
 * @property \Illuminate\Support\Carbon|null            $deleted_at
 *
 * @property-read bool                                  $is_archived
 * @property-read ContactPersonProfile                  $person
 * @property-read ContactCompanyProfile                 $company
 * @property-read ContactCategory                       $category
 * @property-read ContactStatus                         $latestStatus
 * @property-read Photo                                 $avatar
 * @property-read Collection|InsurerContract[]          $contracts
 * @property-read Collection|CompanyGroup[]             $groups
 * @property-read Collection|Note[]                     $notes
 * @property-read Collection|Tag[]                      $tags
 * @property-read Collection|Address[]                  $addresses
 * @property-read Collection|Contact[]                  $subsidiaries
 * @property-read Collection|Contact[]                  $headoffices
 * @property-read Collection|User[]                     $managedAccounts
 * @property-read Collection|Job[]                      $assignedJobs
 * @property-read Collection|JobContactAssignmentType[] $assignmentTypes
 * @property-read Collection|UserNotification[]         $notifications
 * @property-read Collection|PurchaseOrder[]            $purchaseOrders
 *
 * @method static \Illuminate\Database\Eloquent\Builder shouldBeDeactivated()
 *
 * @OA\Schema (
 *     type="object",
 *     required={"id","contact_type","contact_category_id","last_active_at","created_at","updated_at"}
 * )
 */
class Contact extends Model
{
    use SoftDeletes, ApiRequestFillable, Searchable, HasLatestStatus;

    protected $touches = ['assignedJobs'];

    /** Number of inactive days after which contact should be deactivated */
    public const INACTIVE_DAYS_COUNT = 90;

    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="Contact identifier",
     *     example=1
     * ),
     * @OA\Property(
     *     property="contact_type",
     *     type="string",
     *     description="Contact type: person or company",
     *     example="person"
     * ),
     * @OA\Property(
     *     property="contact_category_id",
     *     type="integer",
     *     description="Contact category identifier",
     *     example=1
     * ),
     * @OA\Property(
     *     property="email",
     *     type="string",
     *     description="Email",
     *     example="john.smith@mail.com.au"
     * ),
     * @OA\Property(
     *     property="business_phone",
     *     type="string",
     *     description="Business phone",
     *     example="0398776000"
     * ),
     * @OA\Property(
     *     property="is_archived",
     *     type="boolean",
     *     description="Means that contact is inactive or not",
     *     example="true"
     * ),
     * @OA\Property(
     *     property="last_active_at",
     *     type="string",
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="created_at",
     *     type="string",
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="updated_at",
     *     type="string",
     *     format="date-time")
     * ,
     */

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
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_active_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'created_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'     => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'     => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_active_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_archived', 'avatar_url',
    ];

    /**
     * Elasticsearch index.
     */
    protected $indexConfigurator = ContactsIndexConfigurator::class;

    /**
     * Elasticsearch mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'id'                    => [
                'type' => 'long',
            ],
            'name'                  => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
                'fielddata'       => true,
                'fields'          => [
                    'raw' => [
                        'type'       => 'keyword',
                        'normalizer' => 'case_insensitive',
                    ],
                ],
            ],
            'email'                 => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
                'fielddata'       => true,
            ],
            'mobile_phone'          => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete',
                'fielddata'       => true,
            ],
            'last_active_timestamp' => [
                'type' => 'integer',
            ],
            'contact_type'          => [
                'type' => 'keyword',
            ],
            'contact_category_id'   => [
                'type' => 'integer',
            ],
            'contact_category_type' => [
                'type' => 'keyword',
            ],
            'contact_status'        => [
                'type' => 'keyword',
            ],
            'data'                  => [
                'enabled' => false,
            ],
        ],
    ];

    /**
     * Defines relationship with contact_person_profiles table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function person(): HasOne
    {
        return $this->hasOne(ContactPersonProfile::class);
    }

    /**
     * Defines relationship with contact_company_profiles table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function company(): HasOne
    {
        return $this->hasOne(ContactCompanyProfile::class);
    }

    /**
     * Defines relationship with contact_categories table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ContactCategory::class, 'contact_category_id');
    }

    /**
     * Defines relationship with contact_statuses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this
            ->hasMany(ContactStatus::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Defines relationship with insurer_contracts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(InsurerContract::class);
    }

    /**
     * Defines relationship with company_groups table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CompanyGroup::class, 'company_group_contact');
    }

    /**
     * Defines relationship with notes table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this
            ->belongsToMany(Note::class, 'contact_note')
            ->whereNull('contact_note.deleted_at')
            ->withPivot([
                'meeting_id',
                'created_at',
            ]);
    }

    /**
     * Defines relationship with tags table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'contact_tag');
    }

    /**
     * Defines relationship with addresses table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function addresses(): BelongsToMany
    {
        return $this
            ->belongsToMany(Address::class, 'address_contact')
            ->withPivot('type');
    }

    /**
     * Defines headoffice/subsidiaries relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subsidiaries(): BelongsToMany
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_company',
            'company_id',
            'contact_id'
        );
    }

    /**
     * Defines subsidiary/headoffices relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function headoffices(): BelongsToMany
    {
        return $this->belongsToMany(
            Contact::class,
            'contact_company',
            'contact_id',
            'company_id'
        );
    }

    /**
     * Defines managed accounts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function managedAccounts(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'managed_accounts',
            'contact_id',
            'user_id'
        );
    }

    /**
     * Latest (or current) contact status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this
            ->hasOne(ContactStatus::class, 'contact_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Returns current status of the job.
     *
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->latestStatus()
            ->value('status');
    }

    /**
     * Get contact name
     *
     * @return null|string
     */
    public function getContactName(): ?string
    {
        if ($this->contact_type === ContactTypes::PERSON) {
            return $this->person->getFullName();
        }
        if ($this->contact_type === ContactTypes::COMPANY) {
            return $this->company->legal_name;
        }

        return null;
    }

    /**
     * Returns formatted id.
     *
     * @return string
     */
    public function getFormattedId(): string
    {
        return '#' . $this->id;
    }

    /**
     * Assigned jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedJobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Job::class,
                'job_contact_assignments',
                'assignee_contact_id',
                'job_id'
            )
            ->withPivot(['job_assignment_type_id', 'assigner_id', 'invoice_to']);
    }

    /**
     * Assignment types.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignmentTypes(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                JobContactAssignmentType::class,
                'job_contact_assignments',
                'assignee_contact_id',
                'job_assignment_type_id'
            )
            ->withPivot(['job_id', 'assigner_id', 'invoice_to', 'job_assignment_type_id']);
    }

    /**
     * Returns all notifications for contact.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                UserNotification::class,
                'contact_user_notification',
                'contact_id',
                'user_notification_id'
            );
    }

    /**
     * Relationship with purchase orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'recipient_contact_id');
    }

    /**
     * Get parent company / head office.
     * Contacts cannot be assigned to more than one parent company, so this method returns first one.
     *
     * @return \App\Components\Contacts\Models\Contact|null
     */
    public function getParentCompany(): ?self
    {
        $parents = $this->headoffices()
            ->with('addresses')
            ->get();

        return count($parents) > 0 ? $parents[0] : null;
    }

    /**
     * Contact's avatar.
     *
     * @return BelongsTo
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'avatar_photos_id');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $result['data']                  = $this->toArray();
        $result['id']                    = $this->id;
        $result['email']                 = $this->email;
        $result['last_active_timestamp'] = Carbon::parse($this->last_active_at)->timestamp;
        $result['contact_type']          = $result['data']['contact_type'];
        $result['contact_category_id']   = $result['data']['contact_category_id'];
        $result['contact_category_type'] = $this->category->type;
        $result['data'] ['has_alerts']   = $this->tags()->where('is_alert', true)->exists();
        if ($this->latestStatus()->exists()) {
            $result['contact_status_id']      = $this->latestStatus->id;
            $result['data']['contact_status'] = [
                'id'     => $this->latestStatus->id,
                'status' => $this->latestStatus->status,
            ];
        }
        $result['data']['contact_category'] = [
            'id'   => $this->category->id,
            'name' => $this->category->name,
            'type' => $this->category->type,
        ];
        $result['data']['addresses']        = $this->addresses->toArray();
        if (isset($this->person)) {
            $result         += [
                'name'         => $this->person->getFullName(),
                'mobile_phone' => $this->person->mobile_phone,
            ];
            $result['data'] += [
                'first_name'   => $this->person->first_name,
                'last_name'    => $this->person->last_name,
                'job_title'    => $this->person->job_title,
                'direct_phone' => $this->person->direct_phone,
                'mobile_phone' => $this->person->mobile_phone,
            ];
        } elseif (isset($this->company)) {
            $result         += ['name' => $this->company->legal_name];
            $result['data'] += [
                'legal_name'                 => $this->company->legal_name,
                'trading_name'               => $this->company->trading_name,
                'abn'                        => $this->company->abn,
                'website'                    => $this->company->website,
                'default_payment_terms_days' => $this->company->default_payment_terms_days,
            ];
        }

        return $result;
    }

    /**
     * Allows to filter contacts.
     *
     * @param array $options
     *
     * @return ScoutBuilder
     */
    public static function filter(array $options): ScoutBuilder
    {
        if (!isset($options['term']) || empty($options['term'])) {
            $options['term'] = '*';
        }

        $query = static::search($options['term']);

        if (isset($options['active_in_days'])) {
            $query->where(
                'last_active_timestamp',
                '>=',
                Carbon::now()->subDays($options['active_in_days'])->timestamp
            );
        }

        if (isset($options['contact_type'])) {
            $query->where('contact_type', $options['contact_type']);
        }

        if (isset($options['contact_category_id'])) {
            $query->where('contact_category_id', $options['contact_category_id']);
        }

        if (isset($options['contact_category_type'])) {
            $query->where('contact_category_type', $options['contact_category_type']);
        }

        if (isset($options['contact_status'])) {
            // query is the same as in HasLatestStatus trait. ElasticQuery is not support closure in whereIn
            $sql = 'SELECT contact_id
                    FROM (
                           SELECT contacts.id AS contact_id,
                                  (
                                    SELECT status
                                    FROM contact_statuses
                                    WHERE contact_id = contacts.id
                                    ORDER BY id DESC
                                    LIMIT 1
                                  ) AS latest_status
                           FROM contacts
                         ) AS subQuery
                    WHERE latest_status = ?';

            $contactsIds = collect(DB::select($sql, [$options['contact_status']]))
                ->pluck('contact_id')
                ->toArray();

            $query->whereIn('id', $contactsIds);
        }

        $query->orderBy('name.raw');

        return $query;
    }

    /**
     * @return bool
     */
    public function getIsArchivedAttribute(): bool
    {
        $archivedDate = Carbon::now()->subDays(self::INACTIVE_DAYS_COUNT);

        return $this->last_active_at ? $this->last_active_at->lt($archivedDate) : true;
    }

    /**
     * Get URL to the contact's avatar image.
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar()->exists() ? $this->avatar->url : null;
    }

    /**
     * Scope a query to include contacts that should be deactivated.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeShouldBeDeactivated(Builder $query): Builder
    {
        $deactivateDay = Carbon::now()->subDays(self::INACTIVE_DAYS_COUNT);

        return $query->whereNotIn(
            'id',
            $this->getEntityIdsWhereLatestStatusIs('contacts', [ContactStatuses::INACTIVE])
        )->whereDate('last_active_at', '<', $deactivateDay);
    }

    /**
     * Returns mailing address.
     *
     * @return \App\Components\Addresses\Models\Address|null
     */
    public function getMailingAddress(): ?Address
    {
        return $this->addresses->filter(function ($item) {
            return $item->pivot->type === AddressContactTypes::MAILING;
        })->first();
    }

    /**
     * Returns address of the contact. Attempts to find mailing address and if it's not exists
     * attempts to find any address attached to the contact.
     *
     * @return Address|null
     */
    public function getAddress(): ?Address
    {
        $address = $this->getMailingAddress();
        if (null === $address) {
            $address = $this->addresses->first();
        }

        return $address;
    }

    /**
     * Allows to change status of the contact.
     *
     * @param string $status New status.
     *
     * @return \App\Components\Contacts\Models\ContactStatus
     *
     */
    public function changeStatus(string $status): ContactStatus
    {
        if (!in_array($status, ContactStatuses::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status %s specified, allowed values are: %s',
                $status,
                implode(',', ContactStatuses::values())
            ));
        }

        /** @var \App\Components\Contacts\Models\ContactStatus $createdStatus */
        $createdStatus = $this->statuses()->create([
            'status' => $status,
        ]);

        return $createdStatus;
    }
}
