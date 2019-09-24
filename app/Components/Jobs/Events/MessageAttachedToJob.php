<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use App\Components\Messages\Models\Message;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class MessageAttachedToJob
 *
 * @package App\Components\Jobs\Events
 */
class MessageAttachedToJob extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'job.message_attached';
    // <name> has sent a reply to job #<number>-code
    public const TEXT = '%s has sent a reply to job %s';

    /**
     * Create a new event instance.
     *
     * @param Job     $job
     * @param Message $message
     */
    public function __construct(Job $job, Message $message)
    {
        $this->targetModel  = $job;
        $this->contextModel = $message;
        $this->senderId     = $message->sender_user_id;
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
        $message = Message::find($this->contextModel->id);
        $sender  = $this->getSender();

        if (null === $sender) {
            $args = null === $message
                ? ['Someone', $this->targetModel->getFormattedId()]
                : [
                    $message->from_name ?? $message->from_address ?? 'Someone',
                    $this->targetModel->getFormattedId(),
                ];
        } else {
            if ($recipient->id === $sender->id) {
                return '';
            }

            $args = [
                $sender->full_name ?? $sender->email,
                $this->targetModel->getFormattedId()
            ];
        }

        $text = vsprintf(self::TEXT, $args);

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType())
            ->setContext($this->getContextId(), $this->getContextType());

        return json_encode($body->toArray());
    }
}
