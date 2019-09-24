<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class JobUpdated
 *
 * @package App\Components\Jobs\Events
 */
class JobUpdated extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'job.updated';
    //<name> updated details in job #<number>-code
    public const TEXT = '%s updated details in job %s';

    /**
     * JobUpdated constructor.
     *
     * @param \App\Components\Jobs\Models\Job $job
     * @param int|null                        $senderId
     */
    public function __construct(Job $job, ?int $senderId)
    {
        $this->targetModel = $job;
        $this->senderId    = $senderId;
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
        $sender = null;
        if (null !== $this->senderId) {
            if ($recipient->id === $this->senderId) {
                return '';
            }
            $sender = $this->getSender();
            $args   = [
                $sender->full_name ?? $sender->email,
                $this->targetModel->getFormattedId(),
            ];
        } else {
            $args = ['Reply via email', $this->targetModel->getFormattedId()];
        }

        $text = vsprintf(self::TEXT, $args);

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType());

        return json_encode($body->toArray());
    }
}
