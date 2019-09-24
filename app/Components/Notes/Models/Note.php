<?php

namespace App\Components\Notes\Models;

use App\Components\Contacts\Models\Contact;
use App\Components\Documents\Models\Document;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Components\Meetings\Models\Meeting;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Models\User;
use App\Models\UserMentionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class Note
 *
 * @property int                             $id
 * @property string                          $note
 * @property string                          $note_resolved
 * @property int|null                        $user_id
 * @property Carbon                          $created_at
 * @property Carbon                          $updated_at
 * @property-read Collection|User[]          $mentionedUsers
 * @property-read Collection|Document[]      $documents
 * @property-read Collection|Contact[]       $contacts
 * @property-read Collection|Job[]           $jobs
 * @property-read User                       $user
 * @property-read Collection|PurchaseOrder[] $purchaseOrders
 * @property-read Collection|Equipment[]     $equipment
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","user_id","note"}
 * )
 */
class Note extends Model
{
    use UserMentionable;

    /**
     * @OA\Property(property="id", type="integer", description="Note identifier", example=1)
     * @OA\Property(property="user_id", type="integer", description="User identifier", example=1)
     * @OA\Property(property="note", type="string", description="Note text", example="Hello")
     * @OA\Property(
     *     property="note_resolved",
     *     type="string",
     *     nullable=true,
     *     description="Note text with resolved mentions of users",
     *     example="Hello @John_Smith"
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     */

    public $timestamps = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
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
    ];

    /**
     * Author of the note.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Define documents relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_note');
    }

    /**
     * Define contacts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function contacts(): BelongsToMany
    {
        return $this
            ->belongsToMany(Contact::class, 'contact_note')
            ->withPivot('meeting_id');
    }

    /**
     * Define meetings relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'contact_note');
    }

    /**
     * Jobs to which this note attached.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Job::class,
                'job_notes',
                'note_id',
                'job_id'
            )
            ->whereNull('job_notes.deleted_at')
            ->withPivot(['job_status_id', 'created_at']);
    }

    /**
     * Define mentioned users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mentionedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'note_mentions');
    }

    /**
     * Purchase orders to which this note attached.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(
            PurchaseOrder::class,
            'note_purchase_order',
            'note_id',
            'purchase_order_id'
        );
    }

    /**
     * Equipment to which this note attached.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(
            PurchaseOrder::class,
            'equipment_note',
            'note_id',
            'equipment_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getUnresolvedText(): string
    {
        return $this->note;
    }

    /**
     * {@inheritdoc}
     */
    protected function setResolvedText(string $text)
    {
        $this->note_resolved = $text;
    }
}
