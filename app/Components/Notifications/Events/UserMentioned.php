<?php

namespace App\Components\Notifications\Events;

use App\Components\Jobs\Models\Job;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserMentioned
 *
 * @package App\Components\Jobs\Events
 */
class UserMentioned extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'user_mentioned';
    //<name> mentioned you in job|contact #<number>
    public const TEXT = '%s mentioned you in %s #%s%s';

    /** @var array mentioned user ids */
    public $mentionedUserIds;

    /**
     * UserMentioned constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $targetModel
     * @param \Illuminate\Database\Eloquent\Model $contextModel
     * @param array                               $mentionedUserIds
     * @param int|null                            $mentionAuthorId
     */
    public function __construct(
        Model $targetModel,
        Model $contextModel,
        array $mentionedUserIds,
        int $mentionAuthorId = null
    ) {
        $this->targetModel      = $targetModel;
        $this->contextModel     = $contextModel;
        $this->senderId         = $mentionAuthorId;
        $this->mentionedUserIds = $mentionedUserIds;
    }

    /**
     * @return string
     */
    public function getNotificationType(): string
    {
        return self::TYPE;
    }

    /**
     * @param \App\Models\User $recipient
     *
     * @return string
     */
    public function getBodyText(User $recipient): string
    {
        if (!in_array($recipient->id, $this->mentionedUserIds)) {
            return '';
        }

        $targetType = $this->getTargetType();

        $locationCode = '';
        if ($this->getTargetType() === 'job') {
            $job = Job::find($this->targetModel->id);

            $locationCode = null === $job->assigned_location_id
                ? ''
                : '-' . $job->assignedLocation->code;
        }

        $sender = $this->getSender();

        $text = null === $sender
            ? vsprintf(self::TEXT, ['Someone', $targetType, $this->targetModel->id, $locationCode])
            : vsprintf(
                self::TEXT,
                [($sender->full_name) ?? $sender->email, $targetType, $this->targetModel->id, $locationCode]
            );

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType())
            ->setContext($this->getContextId(), $this->getContextType());

        return json_encode($body->toArray());
    }

    /**
     * Returns context type. f.i. note, message in the job.
     *
     * @return string|null
     */
    public function getContextType(): string
    {
        return str_singular($this->contextModel->getTable());
    }

    /**
     * Returns context entity id.
     *
     * @return int|null
     */
    public function getContextId(): ?int
    {
        return $this->contextModel->id;
    }
}
