<?php

namespace App\Components\Jobs\Models;

use App\Components\Addresses\Models\Address;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Documents\Models\Document;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\JobsIndexConfigurator;
use App\Components\Jobs\JobsSearchRules;
use App\Components\Locations\Models\Location;
use App\Components\Messages\Enums\SpecialJobContactAssignmentTypes;
use App\Components\Messages\Models\Message;
use App\Components\Notes\Models\Note;
use App\Components\Photos\Models\Photo;
use App\Components\Notifications\Models\UserNotification;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\Tags\Models\Tag;
use App\Components\Teams\Models\Team;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\InsurerContract;
use App\Models\ApiRequestFillable;
use App\Models\DateTimeFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use ScoutElastic\Searchable;

/**
 * Class Job
 *
 * @package App\Components\Jobs\Models
 *
 * @property int                                   $id
 * @property string|null                           $claim_number
 * @property int|null                              $job_service_id
 * @property int|null                              $insurer_id
 * @property int|null                              $insurer_contract_id
 * @property int|null                              $site_address_id
 * @property float|null                            $site_address_lat
 * @property float|null                            $site_address_lng
 * @property int|null                              $assigned_location_id
 * @property int|null                              $owner_location_id
 * @property int|null                              $recurring_job_id
 * @property string|null                           $reference_number
 * @property string|null                           $claim_type
 * @property string|null                           $criticality
 * @property Carbon|null                           $date_of_loss
 * @property Carbon|null                           $initial_contact_at
 * @property string|null                           $cause_of_loss
 * @property string|null                           $description
 * @property float|null                            $anticipated_revenue
 * @property Carbon|null                           $anticipated_invoice_date
 * @property Carbon|null                           $authority_received_at
 * @property float|null                            $expected_excess_payment
 * @property Carbon                                $created_at
 * @property Carbon                                $updated_at
 * @property Carbon|null                           $pinned_at
 * @property Carbon|null                           $touched_at
 * @property Carbon|null                           $snoozed_until
 * @property Carbon|null                           $deleted_at
 *
 * @property-read Contact                          $insurer
 * @property-read InsurerContract                  $insurerContract
 * @property-read Address                          $siteAddress
 * @property-read Collection|JobStatus[]           $statuses
 * @property-read JobStatus                        $latestStatus
 * @property-read JobService                       $service
 * @property-read Job                              $mergedToJob
 * @property-read Location                         $ownerLocation
 * @property-read Location                         $assignedLocation
 * @property-read JobTask                          $nextTask
 * @property-read Collection|User[]                $assignedUsers
 * @property-read Collection|Team[]                $assignedTeams
 * @property-read Collection|User[]                $followers
 * @property-read Collection|Tag[]                 $tags
 * @property-read Collection|Note[]                $notes
 * @property-read Collection|Document[]            $documents
 * @property-read Collection|Message[]             $messages
 * @property-read Collection|Message[]             $incomingMessages
 * @property-read Collection|Message[]             $outgoingMessages
 * @property-read Collection|Contact[]             $assignedContacts
 * @property-read Collection|Job[]                 $linkedJobs
 * @property-read Collection|JobLabour[]           $labours
 * @property-read Collection|JobMaterial[]         $materials
 * @property-read Collection|JobReimbursement[]    $reimbursements
 * @property-read Collection|JobAllowance[]        $allowances
 * @property-read Collection|JobLahaCompensation[] $compensations
 * @property-read Collection|Photo[]               $photos
 * @property-read Collection|UserNotification[]    $notifications
 * @property-read Collection|SiteSurveyQuestion[]  $siteSurveyQuestions
 * @property-read Collection|JobRoom[]             $jobRooms
 * @property-read Collection|JobTask[]             $tasks
 * @property-read Collection|PurchaseOrder[]       $purchaseOrders
 * @property-read Collection|Equipment[]           $equipment
 * @property-read Collection|AssessmentReport[]    $assessmentReports
 *
 * @method static Builder|Job whereAccountingOrganizationId($value)
 * @method static Builder|Job whereAnticipatedInvoiceDate($value)
 * @method static Builder|Job whereAnticipatedRevenue($value)
 * @method static Builder|Job whereAssignedLocationId($value)
 * @method static Builder|Job whereAuthorityReceivedAt($value)
 * @method static Builder|Job whereCauseOfLoss($value)
 * @method static Builder|Job whereClaimNumber($value)
 * @method static Builder|Job whereClaimType($value)
 * @method static Builder|Job whereCreatedAt($value)
 * @method static Builder|Job whereCriticality($value)
 * @method static Builder|Job whereDateOfLoss($value)
 * @method static Builder|Job whereDeletedAt($value)
 * @method static Builder|Job whereDescription($value)
 * @method static Builder|Job whereExpectedExcessPayment($value)
 * @method static Builder|Job whereId($value)
 * @method static Builder|Job whereInitialContactAt($value)
 * @method static Builder|Job whereInsurerContractId($value)
 * @method static Builder|Job whereInsurerId($value)
 * @method static Builder|Job whereJobServiceId($value)
 * @method static Builder|Job whereMergedToId($value)
 * @method static Builder|Job whereModifiedAt($value)
 * @method static Builder|Job whereOwnerLocationId($value)
 * @method static Builder|Job wherePinnedAt($value)
 * @method static Builder|Job whereRecurringJobId($value)
 * @method static Builder|Job whereReferenceNumber($value)
 * @method static Builder|Job whereSiteAddressId($value)
 * @method static Builder|Job whereSiteAddressLat($value)
 * @method static Builder|Job whereSiteAddressLng($value)
 * @method static Builder|Job whereUpdatedAt($value)
 * @method static Builder|Job whereSnoozedUntil($value)
 * @method static Builder|Job shouldBeUnsnoozed()
 *
 * @mixin \Eloquent
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id"}
 * )
 */
class Job extends Model
{
    use ApiRequestFillable, DateTimeFillable, SoftDeletes, Searchable;

    /**
     * @OA\Property(
     *     property="id",
     *     description="Job Identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="claim_number",
     *     description="Claim number",
     *     type="string",
     *     nullable=true,
     *     example="#10198747-MEL"
     * ),
     * @OA\Property(
     *     property="job_service_id",
     *     description="Identifier of related service",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="insurer_id",
     *     description="Identifier of insurer",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="insurer_contract_id",
     *     description="Identifier of insurer contract",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="site_address_id",
     *     description="Identifier of site address",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="site_address_lat",
     *     description="Latitude of site address",
     *     type="number",
     *     example="-37.815018"
     * ),
     * @OA\Property(
     *     property="site_address_lng",
     *     description="Longitude of site address",
     *     type="number",
     *     example="144.946014"
     * ),
     * @OA\Property(
     *     property="assigned_location_id",
     *     description="Identifier of assigned location",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="owner_location_id",
     *     description="Identifier of owner location",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="reference_number",
     *     description="Reference number",
     *     type="string",
     *     example="#reference_number"
     * ),
     * @OA\Property(
     *     property="claim_type",
     *     ref="#/components/schemas/ClaimTypes"
     * ),
     * @OA\Property(
     *     property="criticality",
     *     ref="#/components/schemas/JobCriticalityTypes"
     * ),
     * @OA\Property(
     *     property="date_of_loss",
     *     description="Date of loss",
     *     type="string",
     *     format="date",
     *     example="2018-11-10"
     * ),
     * @OA\Property(
     *     property="initial_contact_at",
     *     description="Initial contact at",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *     property="cause_of_loss",
     *     description="Cause of loss",
     *     type="string",
     *     example="Some cause"
     * ),
     * @OA\Property(
     *     property="description",
     *     description="Description",
     *     type="string",
     *     example="Clean up water, dry out kitchen cabinetry and timber flooring"
     * ),
     * @OA\Property(
     *     property="anticipated_revenue",
     *     description="Anticipated revenue",
     *     type="number",
     *     example="5000"
     * ),
     * @OA\Property(
     *     property="anticipated_invoice_date",
     *     description="Anticipated invoice date",
     *     type="string",
     *     format="date",
     *     example="2018-11-10"
     * ),
     * @OA\Property(
     *     property="recurring_job_id",
     *     description="Recurring job identifier",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="expected_excess_payment",
     *     description="Expected excess payment",
     *     type="number",
     *     example="1000"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time"),
     * @OA\Property(property="updated_at", type="string", format="date-time"),
     * @OA\Property(
     *     property="pinned_at",
     *     description="Time when job was pinned",
     *     type="string",
     *     nullable=true,
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="touched_at",
     *     description="Shows when job was modified for the last time (for example job reply was received)",
     *     type="string",
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="snoozed_until",
     *     description="Time until which job is snoozed",
     *     type="string",
     *     nullable=true,
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="authority_received_at",
     *     description="Authority received at",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *     property="deleted_at",
     *     type="string",
     *     nullable=true,
     *     format="date-time"
     * ),
     */

    protected $indexConfigurator = JobsIndexConfigurator::class;

    protected $searchRules = [
        JobsSearchRules::class,
    ];

    /**
     * Elasticsearch mapping for a model fields.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'id'     => [
                'type' => 'text',
            ],
            'status' => [
                'type' => 'text',
            ],
            'data'   => [
                'enabled' => false,
            ],
        ],
    ];

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
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_of_loss'             => 'datetime:Y-m-d',
        'initial_contact_at'       => 'datetime:Y-m-d\TH:i:s\Z',
        'anticipated_invoice_date' => 'datetime:Y-m-d',
        'authority_received_at'    => 'datetime:Y-m-d\TH:i:s\Z',
        'created_at'               => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at'               => 'datetime:Y-m-d\TH:i:s\Z',
        'pinned_at'                => 'datetime:Y-m-d\TH:i:s\Z',
        'touched_at'               => 'datetime:Y-m-d\TH:i:s\Z',
        'snoozed_until'            => 'datetime:Y-m-d\TH:i:s\Z',
        'deleted_at'               => 'datetime:Y-m-d\TH:i:s\Z',
        'expected_excess_payment'  => 'float',
        'anticipated_revenue'      => 'float',
        'site_address_lat'         => 'float',
        'site_address_lng'         => 'float',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_of_loss',
        'initial_contact_at',
        'anticipated_invoice_date',
        'authority_received_at',
        'created_at',
        'updated_at',
        'pinned_at',
        'touched_at',
        'snoozed_until',
        'deleted_at',
    ];

    /**
     * Properties whose modification causes updating touched_at column.
     *
     * @var array
     */
    private static $touchedAtTracking = [
        'assigned_location_id',
        'owner_location_id',
        'authority_received_at',
        'job_service_id',
        'description',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (Job $job) {
            if ($job->isDirty(self::$touchedAtTracking)) {
                $job->touched_at = Carbon::now();
            }
        });
    }

    /**
     * Insurer company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'insurer_id');
    }

    /**
     * Insurer contract.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function insurerContract(): BelongsTo
    {
        return $this->belongsTo(InsurerContract::class, 'insurer_contract_id');
    }

    /**
     * Job statuses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this
            ->hasMany(JobStatus::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Job tasks.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(JobTask::class);
    }

    /**
     * Next one by time task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function nextTask(): HasOne
    {
        return $this
            ->hasOne(JobTask::class)
            ->whereRaw('starts_at > now()')
            ->orderBy('starts_at');
    }

    /**
     * Latest (or current) job status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this
            ->hasOne(JobStatus::class, 'job_id')
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
     * Job service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(JobService::class, 'job_service_id');
    }

    /**
     * Owner location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function ownerLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'owner_location_id');
    }

    /**
     * Assigned location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'assigned_location_id');
    }

    /**
     * Site address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function siteAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'site_address_id');
    }

    /**
     * Assigned users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'job_user_assignments',
            'job_id',
            'user_id'
        );
    }

    /**
     * Assigned teams.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedTeams(): BelongsToMany
    {
        return $this->belongsToMany(
            Team::class,
            'job_team_assignments',
            'job_id',
            'team_id'
        );
    }

    /**
     * Job followers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'job_followers',
            'job_id',
            'user_id'
        );
    }

    /**
     * Job tags.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'job_tag',
            'job_id',
            'tag_id'
        );
    }

    /**
     * Job notes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function notes(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Note::class,
                'job_notes',
                'job_id',
                'note_id'
            )
            ->whereNull('job_notes.deleted_at')
            ->withPivot(['job_status_id', 'created_at'])
            ->orderByDesc('job_notes.created_at');
    }

    /**
     * Job documents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documents(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Document::class,
                'job_documents',
                'job_id',
                'document_id'
            )
            ->withPivot(['creator_id', 'type', 'description', 'created_at', 'updated_at']);
    }

    /**
     * All job messages (incoming + outgoing).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function messages(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Message::class,
                'job_messages',
                'job_id',
                'message_id'
            )
            ->whereNull('job_messages.deleted_at')
            ->withPivot(['created_at', 'read_at'])
            ->orderByDesc('job_messages.created_at');
    }

    /**
     * Incoming job messages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function incomingMessages(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Message::class,
                'job_messages',
                'job_id',
                'message_id'
            )
            ->where('is_incoming', '=', true)
            ->whereNull('job_messages.deleted_at')
            ->withPivot(['created_at', 'read_at'])
            ->orderByDesc('job_messages.created_at');
    }

    /**
     * Outgoing job messages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function outgoingMessages(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Message::class,
                'job_messages',
                'job_id',
                'message_id'
            )
            ->where('is_incoming', '=', false)
            ->whereNull('job_messages.deleted_at')
            ->withPivot(['created_at']);
    }

    /**
     * Allows to change status of this job.
     *
     * @param string      $status New status.
     * @param string|null $note   Optional reason for status change.
     * @param int|null    $userId Optional id of user who is changing status.
     *
     * @return \App\Components\Jobs\Models\JobStatus
     *
     * @throws \Throwable
     */
    public function changeStatus(string $status, string $note = null, int $userId = null): JobStatus
    {
        if (!in_array($status, JobStatuses::values())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status %s specified, allowed values are: %s',
                $status,
                implode(',', JobStatuses::values())
            ));
        }

        /** @var \App\Components\Jobs\Models\JobStatus $createdStatus */
        $createdStatus = $this->statuses()->create([
            'status'  => $status,
            'note'    => $note,
            'user_id' => $userId,
        ]);

        return $createdStatus;
    }

    /**
     * Assigned contacts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assignedContacts(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Contact::class,
                'job_contact_assignments',
                'job_id',
                'assignee_contact_id'
            )
            ->withPivot(['job_assignment_type_id', 'assigner_id', 'invoice_to', 'created_at']);
    }

    /**
     * Returns linked jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function linkedJobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                __CLASS__,
                'job_links',
                'job_id',
                'linked_job_id'
            );
    }

    /**
     * Returns merged jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mergedJobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                __CLASS__,
                'merged_jobs',
                'source_job_id',
                'destination_job_id'
            );
    }

    /**
     * Returns attached photos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class)->withPivot([
            'creator_id',
            'modified_by_id',
            'description',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * Returns all notifications for job.
     */
    public function notifications(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                UserNotification::class,
                'job_user_notification',
                'job_id',
                'user_notification_id'
            );
    }

    /**
     * Returns attached survey site questions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function siteSurveyQuestions(): BelongsToMany
    {
        return $this->belongsToMany(
            SiteSurveyQuestion::class,
            'job_site_survey_questions',
            'job_id',
            'site_survey_question_id'
        )->where('is_active', true)
            ->withPivot([
                'site_survey_question_option_id',
                'answer',
            ]);
    }

    /**
     * Returns job rooms.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobRooms(): HasMany
    {
        return $this->hasMany(JobRoom::class)
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Relationship with purchase orders table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'job_id');
    }

    /**
     * Returns all recurring jobs.
     */
    public function recurringJobs(): BelongsTo
    {
        return $this->belongsTo(
            RecurringJob::class,
            'recurring_job_id'
        );
    }

    /**
     * Materials for a job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function materials(): HasMany
    {
        return $this->hasMany(JobMaterial::class, 'job_id')
            ->orderByDesc('id');
    }

    /**
     * Allowances for a job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allowances(): HasMany
    {
        return $this->hasMany(JobAllowance::class, 'job_id')
            ->orderByDesc('id');
    }

    /**
     * Reimbursements for a job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reimbursements(): HasMany
    {
        return $this->hasMany(JobReimbursement::class, 'job_id')
            ->orderByDesc('id');
    }

    /**
     * LAHA compensations for a job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function compensations(): HasMany
    {
        return $this->hasMany(JobLahaCompensation::class, 'job_id')
            ->orderByDesc('id');
    }

    /**
     * Labour for a job.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function labours(): HasMany
    {
        return $this->hasMany(JobLabour::class, 'job_id')
            ->orderByDesc('id');
    }

    /**
     * Equipment which the job are used.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function equipment(): HasMany
    {
        return $this->hasMany(JobEquipment::class, 'job_id')
            ->orderByDesc('id');
    }

    /**
     * Relationship with assessment reports table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assessmentReports(): HasMany
    {
        return $this->hasMany(AssessmentReport::class)
            ->orderByDesc('id');
    }

    /**
     * Returns all previous jobs created from recurring job.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPreviousJobs(): Collection
    {
        return $this->newQuery()
            ->where('recurring_job_id', $this->recurring_job_id)
            ->whereNotNull('recurring_job_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Update touched_at column and, optionally, save the model.
     *
     * @param bool $save
     *
     * @throws \Throwable
     */
    public function updateTouchedAt($save = true): void
    {
        $this->touched_at = Carbon::now();
        if ($save) {
            $this->saveOrFail();
        }
    }

    /**
     * Setter for initial_contact_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return \App\Components\Jobs\Models\Job
     * @throws \Throwable
     */
    public function setInitialContactAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('initial_contact_at', $datetime);
    }

    /**
     * Setter for authority_received_at attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return \App\Components\Jobs\Models\Job
     * @throws \Throwable
     */
    public function setAuthorityReceivedAtAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('authority_received_at', $datetime);
    }

    /**
     * Setter for snoozed_until attribute.
     *
     * @param Carbon|string $datetime
     *
     * @return \App\Components\Jobs\Models\Job
     * @throws \Throwable
     */
    public function setSnoozedUntilAttribute($datetime): self
    {
        return $this->setDateTimeAttribute('snoozed_until', $datetime);
    }

    /**
     * Check if the job is closed or cancelled.
     *
     * @return bool
     */
    public function isClosed(): bool
    {
        return in_array($this->getCurrentStatus(), JobStatuses::$closedStatuses);
    }

    /**
     * Check if the job can be reopened
     *
     * @return bool
     */
    public function canBeReopened(): bool
    {
        return $this->isClosed();
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    public function reopen(): bool
    {
        if ($this->canBeReopened()) {
            $this->changeStatus(JobStatuses::IN_PROGRESS);

            return true;
        }

        return false;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $result['id']     = $this->id;
        $result['status'] = $this->latestStatus->status;
        $result['data']   = $this->toArray();

        $insurerData = $this->insurer;
        if (null !== $this->insurer) {
            $insurerData['name']                       = $this->insurer->getContactName();
            $insurerData['default_payment_terms_days'] = ($this->insurer->contact_type === ContactTypes::COMPANY) ?
                $this->insurer->company->default_payment_terms_days : null;
        }

        $invoiceToContact = $this->assignedContacts()
            ->where('invoice_to', '=', true)
            ->first();

        $invoiceToContactData = null !== $invoiceToContact
            ? [
                'contact_id'   => $invoiceToContact->id,
                'contact_name' => $invoiceToContact->getContactName(),
            ]
            : null;

        $additionalData = [
            'insurer'            => $insurerData,
            'insurer_contract'   => $this->insurerContract,
            'statuses'           => $this->statuses,
            'latest_status'      => $this->latestStatus,
            'service'            => $this->service,
            'assigned_location'  => $this->assignedLocation,
            'owner_location'     => $this->ownerLocation,
            'site_address'       => $this->siteAddress,
            'followers'          => $this->followers,
            'invoice_to_contact' => $invoiceToContactData,
        ];
        $result['data'] = array_merge($result['data'], $additionalData);

        return $result;
    }

    /**
     * Allows to search jobs for numbers (ids).
     *
     * @param array     $options       Array that should contain number of job.
     * @param bool|null $includeClosed Defines whether closed jobs should be included to result set or not.
     * @param int       $perPage       Defines how many jobs are being shown per page.
     *
     * @return Collection
     */
    public static function searchForNumbers(array $options, ?bool $includeClosed = false, int $perPage = 10): Collection
    {
        $query = static::search($options);

        if (true !== $includeClosed) {
            $query->whereNotIn('status', array_map('strtolower', JobStatuses::$closedStatuses));
        }

        $raw = $query->take($perPage)
            ->raw();

        return collect(mapElasticResults($raw));
    }

    /**
     * Scope a query to include jobs that should be un-snoozed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeShouldBeUnsnoozed(Builder $query): Builder
    {
        return $query->where('snoozed_until', '<', 'now()');
    }

    /**
     * Returns formatted id.
     *
     * @return string
     */
    public function getFormattedId(): string
    {
        $location = $this->assignedLocation;

        if (null !== $location) {
            return '#' . $this->id . '-' . $location->code;
        }

        return '#' . $this->id;
    }

    /**
     * @return string|null
     */
    public function getLatestMessageByMessageAndNote(): ?string
    {
        $latestMessageQuery = DB::query()
            ->select('messages.message_body_resolved AS message', 'job_messages.created_at AS created_at')
            ->from('job_messages')
            ->leftJoin('messages', 'messages.id', '=', 'job_messages.message_id')
            ->whereRaw('job_id =' . $this->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        $latestNoteQuery = DB::query()
            ->select('notes.note_resolved AS message', 'job_notes.created_at AS created_at')
            ->from('job_notes')
            ->leftJoin('notes', 'notes.id', '=', 'job_notes.note_id')
            ->whereRaw('job_id =' . $this->id)
            ->whereRaw('deleted_at IS NULL')
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($latestMessageQuery && $latestNoteQuery) {
            return Carbon::make($latestMessageQuery->created_at) > Carbon::make($latestNoteQuery->created_at)
                ? $latestMessageQuery->message
                : $latestNoteQuery->message;
        } elseif ($latestMessageQuery) {
            return $latestMessageQuery->message;
        } elseif ($latestNoteQuery) {
            return $latestNoteQuery->message;
        } else {
            return null;
        }
    }

    public function setLatestMessageByMessageAndNote(): void
    {
        $message = $this->getLatestMessageByMessageAndNote();
        if ($message) {
            $this->update(['last_message' => $message]);
        }
    }

    /**
     * Get a contact assigned to the job as site contact.
     *
     * @return \App\Components\Contacts\Models\Contact|null
     */
    public function getSiteContact(): ?Contact
    {
        $contactAssignmentType = JobContactAssignmentType::query()
            ->where('name', '=', SpecialJobContactAssignmentTypes::SITE_CONTACT)
            ->first();

        if (!$contactAssignmentType) {
            return null;
        }

        return $this->assignedContacts->first(function ($contact) use ($contactAssignmentType) {
            return $contact->pivot->job_assignment_type_id === $contactAssignmentType->id;
        });
    }
}
