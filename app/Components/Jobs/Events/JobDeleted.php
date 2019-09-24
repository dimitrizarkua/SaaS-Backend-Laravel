<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class JobDeleted
 *
 * @package App\Components\Jobs\Events
 */
class JobDeleted extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'job.deleted';
    public const TEXT = 'Job %s has been deleted';

    /**
     * Create a new event instance.
     *
     * @param Job      $job
     * @param int|null $senderId
     *
     * @throws \Exception
     */
    public function __construct(Job $job, int $senderId = null)
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
        $text   = vsprintf(self::TEXT, [$this->targetModel->getFormattedId()]);
        $sender = $this->getSender();

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType());

        return json_encode($body->toArray());
    }
}
