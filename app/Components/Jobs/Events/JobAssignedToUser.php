<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class JobAssignedToUser
 *
 * @package App\Components\Jobs\Events
 */
class JobAssignedToUser extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'job.user_assigned';
    //[ You | <name> ] were | was assigned to job #<number>
    public const TEXT = '%s %s assigned to job %s';

    /** @var int */
    public $assignedUserId;

    /**
     * Create a new event instance.
     *
     * @param Job      $job
     * @param int      $assignedUserId
     * @param int|null $senderId
     */
    public function __construct(Job $job, int $assignedUserId, int $senderId = null)
    {
        $this->targetModel    = $job;
        $this->assignedUserId = $assignedUserId;
        $this->senderId       = $senderId;
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
        if (null !== $this->senderId && $recipient->id === $this->senderId) {
            return '';
        }

        $assignedUser = User::find($this->assignedUserId);

        $args = $recipient->id === $this->assignedUserId
            ? ['You', 'were', $this->targetModel->getFormattedId()]
            : [
                $assignedUser->full_name ?? $assignedUser->email,
                'was',
                $this->targetModel->getFormattedId(),
            ];

        $text   = vsprintf(self::TEXT, $args);
        $sender = $this->getSender();

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType());

        return json_encode($body->toArray());
    }
}
