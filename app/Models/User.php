<?php

namespace App\Models;

use App\Components\Contacts\Models\Contact;
use App\Components\Finance\Models\Payment;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobTask;
use App\Components\Locations\Models\Location;
use App\Components\Operations\Models\JobRun;
use App\Components\Photos\Models\Photo;
use App\Components\Notifications\Models\UserNotification;
use App\Components\RBAC\Models\Role;
use App\Components\Search\Models\UserAndTeam;
use App\Components\Teams\Models\Team;
use App\Components\Users\UsersIndexConfigurator;
use App\Components\Users\UsersSearchForMentionsRule;
use App\Helpers\DateHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class User
 *
 * @package App
 * @property int                                $id
 * @property string                             $email
 * @property string|null                        $password
 * @property string|null                        $first_name
 * @property string|null                        $last_name
 * @property string|null                        $full_name
 * @property string|null                        $avatar_url
 * @property int|null                           $avatar_photos_id
 * @property float                              $invoice_approve_limit
 * @property float                              $purchase_order_approve_limit
 * @property float                              $credit_note_approval_limit
 * @property string|null                        $azure_graph_id
 * @property float|null                         $working_hours_per_week
 * @property int|null                           $contact_id
 * @property Carbon                             $created_at
 * @property Carbon                             $updated_at
 *
 * @property-read Collection|Client[]           $clients
 * @property-read Collection|Token[]            $tokens
 * @property-read Collection|Role[]             $roles
 * @property-read Collection|Location[]         $locations
 * @property-read Location                      $primaryLocation
 * @property-read Collection|Job[]              $assignedJobs
 * @property-read Collection|Job[]              $followedJobs
 * @property-read Collection|Team[]             $teams
 * @property-read Collection|UserToken[]        $allTokens
 * @property-read Photo                         $avatar
 * @property-read Collection|UserNotification[] $notifications
 * @property-read Collection|Payment[]          $payments
 * @property-read Collection|JobTask[]          $assignedTasks
 * @property-read Collection|JobRun[]           $assignedRuns
 * @property-read Collection|Contact[]          $managedContacts
 * @property-read Contact                       $contact
 *
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereAzureGraphId($value)
 * @mixin \Eloquent
 *
 * @OA\Schema (required={"id","email","created_at","updated_at"})
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable, ApiRequestFillable, Searchable;

    /**
     * @OA\Property(property="id", type="integer", example="1")
     * @OA\Property(
     *     property="email",
     *     description="Email",
     *     type="string",
     *     example="test@steamatic.com.au"
     * ),
     * @OA\Property(
     *     property="first_name",
     *     description="First name",
     *     type="string",
     *     example="John",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="last_name",
     *     description="Last name",
     *     type="string",
     *     example="Smith",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="full_name",
     *     description="Full name",
     *     type="string",
     *     example="John Smith",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="avatar_url",
     *     description="Avatar image URL",
     *     type="string",
     *     example="http://avatar-image-url",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="invoice_approve_limit",
     *     description="Max amount allowing user to approve invoices",
     *     type="number",
     *     format="float",
     *     example="1.2"
     * ),
     * @OA\Property(
     *     property="purchase_order_approve_limit",
     *     description="Max amount allowing user to approve purchase orders",
     *     type="number",
     *     format="float",
     *     example="1.2"
     * ),
     * @OA\Property(
     *     property="credit_note_approval_limit",
     *     description="Max amount allowing user to approve credit notes",
     *     type="number",
     *     format="float",
     *     example="1.2"
     * ),
     * @OA\Property(
     *     property="working_hours_per_week",
     *     description="Working hours per week",
     *     type="number",
     *     nullable=true,
     *     format="float",
     *     example="1.2"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(
     *     property="contact_id",
     *     type="integer",
     *     description="User's contact identifier",
     *     example=1,
     *     nullable=true,
     * ),
     */

    protected $indexConfigurator = UsersIndexConfigurator::class;

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['usersAndTeamsView'];

    /**
     * Mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'id'               => [
                'type' => 'long',
            ],
            'email'            => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
            ],
            'first_name'       => [
                'type'  => 'text',
                'index' => false,
            ],
            'last_name'        => [
                'type'  => 'text',
                'index' => false,
            ],
            'full_name'        => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
            ],
            'primary_location' => [
                'enabled' => false,
            ],
            'location_ids'     => [
                'type' => 'long',
            ],
            'created_at'       => [
                'type'   => 'date',
                'format' => 'yyyy-MM-dd\'T\'HH:mm:ssZ',
                'index'  => false,
            ],
            'updated_at'       => [
                'type'   => 'date',
                'format' => 'yyyy-MM-dd\'T\'HH:mm:ssZ',
                'index'  => false,
            ],
        ],
    ];

    /**
     * Search rules for model.
     *
     * @var array
     */
    protected $searchRules = [
        UsersSearchForMentionsRule::class,
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'password',
        'created_at',
        'updated_at',
        'id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'azure_graph_id',
        'usersAndTeamsView',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at'                   => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'                   => 'datetime:Y-m-d\TH:i:s\Z',
        'invoice_approve_limit'        => 'float',
        'purchase_order_approve_limit' => 'float',
        'credit_note_approval_limit'   => 'float',
        'working_hours_per_week'       => 'float',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['full_name', 'avatar_url'];

    /**
     * Define many-to-many relationship with roles table through users_roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Locations which user is member of.
     *
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Location::class,
                'location_user',
                'user_id',
                'location_id'
            )
            ->withPivot('primary');
    }

    /**
     * Relationship with users_and_teams_view view.
     *
     * @return HasMany
     */
    public function usersAndTeamsView(): HasMany
    {
        return $this
            ->hasMany(
                UserAndTeam::class,
                'entity_id',
                'id'
            )
            ->where('type', UserAndTeam::TYPE_USER);
    }

    /**
     * Teams which user is member of.
     *
     * @return BelongsToMany
     */
    public function teams(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Team::class,
                'team_user',
                'user_id',
                'team_id'
            );
    }

    /**
     * Primary location of the user.
     *
     * @return BelongsToMany
     */
    public function primaryLocation(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Location::class,
                'location_user',
                'user_id',
                'location_id'
            )
            ->wherePivot('primary', '=', true);
    }

    /**
     * Jobs that assigned to this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedJobs(): BelongsToMany
    {
        return $this->belongsToMany(
            Job::class,
            'job_user_assignments',
            'job_id',
            'user_id'
        );
    }

    /**
     * Jobs that this user follows.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followedJobs(): BelongsToMany
    {
        return $this->belongsToMany(
            Job::class,
            'job_followers',
            'user_id',
            'job_id'
        );
    }

    /**
     * Define relationship with user_tokens table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allTokens(): HasMany
    {
        return $this->hasMany(UserToken::class);
    }

    /**
     * User's avatar.
     *
     * @return BelongsTo
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'avatar_photos_id');
    }

    /**
     * Defines user's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Relationship with payments table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Assigned job tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(
            JobTask::class,
            'job_task_crew_assignments',
            'crew_user_id',
            'job_task_id'
        )->withPivot('assigner_id', 'created_at');
    }

    /**
     * Assigned job runs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedRuns(): BelongsToMany
    {
        return $this->belongsToMany(
            JobRun::class,
            'job_run_crew_assignments',
            'crew_user_id',
            'job_run_id'
        )->withPivot('assigner_id', 'created_at');
    }

    /**
     * Defines managed contacts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function managedContacts(): BelongsToMany
    {
        return $this->belongsToMany(
            Contact::class,
            'managed_accounts',
            'user_id',
            'contact_id'
        )->withPivot('created_at');
    }

    /**
     * Defines relationship with contacts table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * @param string $password
     *
     * @return \App\Models\User
     */
    public function setPassword(string $password): self
    {
        $this->password = Hash::make($password);

        return $this;
    }

    /**
     * Get the user's full name.
     *
     * @return string|null
     */
    public function getFullNameAttribute(): ?string
    {
        $result = '';
        if (!empty($this->first_name)) {
            $result = $this->first_name;
        }
        if (!empty($this->last_name)) {
            if (strlen($result) > 0) {
                $result .= ' ';
            }
            $result .= $this->last_name;
        }

        return strlen($result) > 0 ? $result : null;
    }

    /**
     * Get URL to the user's avatar image.
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar()->exists() ? $this->avatar->url : null;
    }

    /**
     * Get working hours per day.
     *
     * @return float|null
     */
    public function getWorkingHoursPerDay(): ?float
    {
        return null !== $this->working_hours_per_week
            ? $this->working_hours_per_week / DateHelper::WORKING_DAYS_IN_WEEK
            : null;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $array['primary_location'] = $this->primaryLocation()->exists()
            ? $this->primaryLocation()->first()->toArray()
            : null;

        $array['location_ids'] = [];
        foreach ($this->locations as $location) {
            $array['location_ids'][] = $location['id'];
        }

        return $array;
    }

    /**
     * Allows to search for users.
     *
     * @param array $options     Array that should contain name of the users.
     * @param array $locationIds Array with authorized user locations ids
     *
     * @return array
     */
    public static function searchForMentions(array $options, array $locationIds = []): array
    {
        $options['location_ids'] = $locationIds;
        $raw                     = static::search($options)
            ->take(10)
            ->raw();

        return mapElasticResults($raw);
    }
}
