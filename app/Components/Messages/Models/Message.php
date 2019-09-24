<?php

namespace App\Components\Messages\Models;

use App\Components\Documents\Models\Document;
use App\Components\Jobs\Models\Job;
use App\Components\Messages\Enums\MessageStatuses;
use App\Models\User;
use App\Models\UserMentionable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use OpenApi\Annotations as OA;

/**
 * Class Message
 *
 * @package App\Components\Messages\Models
 *
 * @mixin \Eloquent
 * @property int                                $id
 * @property int|null                           $sender_user_id
 * @property string                             $message_type
 * @property string|null                        $from_address
 * @property string|null                        $from_name
 * @property string|null                        $subject
 * @property string                             $message_body
 * @property string                             $message_body_resolved
 * @property string|null                        $external_system_message_id
 * @property boolean                            $is_incoming
 * @property \Illuminate\Support\Carbon         $created_at
 * @property \Illuminate\Support\Carbon         $updated_at
 * @property-read Collection|User[]             $mentionedUsers
 * @property-read Collection|User[]             $sender
 * @property-read Collection|MessageRecipient[] $recipients
 * @property-read Collection|MessageStatus[]    $statuses
 * @property-read Collection|MessageStatus      $latestStatus
 * @property-read Collection|Document[]         $documents
 * @property-read Collection|Job[]              $jobs
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","message_type","message_body","is_incoming","created_at","updated_at"}
 * )
 */
class Message extends Model
{
    use UserMentionable;

    /**
     * @OA\Property(property="id", type="integer", example=1)
     * @OA\Property(property="sender_user_id", type="integer", example=2, nullable=true)
     * @OA\Property(
     *     property="message_type",
     *     description="Message type",
     *     type="string",
     *     enum={"email","sms"},
     *     example="email"
     * )
     * @OA\Property(
     *     property="from_address",
     *     description="Sender address. If not null - should be displayed as sender address.",
     *     type="string",
     *     example="person@example.com",
     *     nullable=true
     * )
     * @OA\Property(
     *     property="from_name",
     *     description="Sender name. If not null - should be displayed as sender name.",
     *     type="string",
     *     example="John Doe",
     *     nullable=true
     * )
     * @OA\Property(
     *     property="subject",
     *     description="Message subject",
     *     type="string",
     *     example="Subject",
     *     nullable=true
     * )
     * @OA\Property(
     *     property="message_body",
     *     description="Message body",
     *     type="string",
     *     example="Hello from Steamatic"
     * )
     * @OA\Property(
     *     property="message_body_resolved",
     *     description="Message body with resolved mentions of users",
     *     type="string",
     *     example="Hello from Steamatic @John_Smith"
     * )
     * @OA\Property(
     *     property="is_incoming",
     *     description="Indicates whether message is incoming or outgoing",
     *     type="boolean",
     *     example=false
     * )
     * @OA\Property(property="created_at", type="string", format="date-time")
     * @OA\Property(property="updated_at", type="string", format="date-time")
     */

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
        'sender_user_id',
        'external_system_message_id',
        'is_incoming',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'external_system_message_id',
    ];

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
     * Sender of the message. For system-originated messages there this won't be defined.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function sender(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * Message recipients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class, 'message_id');
    }

    /**
     * Message statuses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses(): HasMany
    {
        return $this
            ->hasMany(MessageStatus::class, 'message_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    /**
     * Latest (or current) message status.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestStatus(): HasOne
    {
        return $this
            ->hasOne(MessageStatus::class, 'message_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    /**
     * Documents attached to this message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(
            Document::class,
            'document_message',
            'message_id',
            'document_id'
        );
    }

    /**
     * Jobs for which this message.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Job::class,
                'job_messages',
                'message_id',
                'job_id'
            )
            ->whereNull('job_messages.deleted_at');
    }

    /**
     * Define mentioned users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function mentionedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_mentions');
    }

    /**
     * {@inheritdoc}
     */
    protected function getUnresolvedText(): string
    {
        return $this->message_body;
    }

    /**
     * {@inheritdoc}
     */
    protected function setResolvedText(string $text)
    {
        $this->message_body_resolved = $text;
    }

    /**
     * Returns current status of the message. Not to be confused with ::latestStatus()
     * method which returns db model that contains all information about the current status
     * while this method returns only status id.
     *
     * @return string
     */
    public function getCurrentStatus(): string
    {
        return $this->latestStatus()->value('status');
    }

    /**
     * Defines whether this message is draft or not.
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return MessageStatuses::DRAFT === $this->getCurrentStatus();
    }

    /**
     * Defines whether message can be forwarded for delivery to service provider or not.
     *
     * @return bool
     */
    public function canBeForwardedForDeliveryToServiceProvider(): bool
    {
        if ($this->is_incoming) {
            return false;
        }

        $allowedStatuses = [
            MessageStatuses::READY_FOR_DELIVERY,
            MessageStatuses::DELIVERY_FAILED,
        ];

        return in_array($this->getCurrentStatus(), $allowedStatuses, true);
    }

    /**
     * Defines whether message can be sent.
     *
     * @return bool
     */
    public function canBeSent(): bool
    {
        if ($this->is_incoming) {
            return false;
        }

        $allowedStatuses = [
            MessageStatuses::DRAFT,
            MessageStatuses::DELIVERY_FAILED,
        ];

        return in_array($this->getCurrentStatus(), $allowedStatuses, true);
    }

    /**
     * Defines whether message can be edited.
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return !$this->is_incoming && $this->isDraft();
    }

    /**
     * Defines whether message can be deleted from the system or not.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_incoming && $this->isDraft();
    }

    /**
     * Allows to change status of this message.
     *
     * @param string      $status New status.
     * @param string|null $reason Optional reason for status change.
     *
     * @throws \Throwable
     */
    public function changeStatus(string $status, string $reason = null): void
    {
        if (!in_array($status, MessageStatuses::values())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid status %s specified, allowed values are: %s',
                $status,
                implode(',', MessageStatuses::values())
            ));
        }

        $status = $this->statuses()->create(['status' => $status, 'reason' => $reason]);
        $status->saveOrFail();
    }
}
