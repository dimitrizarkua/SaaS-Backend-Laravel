<?php

namespace App\Components\Jobs\Events;

use App\Components\Jobs\Models\Job;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class NoteAttachedToJob
 *
 * @property Job  $targetModel
 * @property Note $contextModel
 *
 * @package App\Components\Jobs\Events
 */
class NoteAttachedToJob extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'job.note_attached';
    //[ You | <name> ] added a note to job #<number>-code
    public const TEXT = '%s added a note to job %s';

    /**
     * Create a new event instance.
     *
     * @param Job  $job
     * @param Note $note
     */
    public function __construct(Job $job, Note $note)
    {
        $this->targetModel  = $job;
        $this->contextModel = $note;
        $this->senderId     = $note->user_id;
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
        if ($recipient->id === $this->senderId) {
            return '';
        }

        $sender = $this->getSender();

        $args = [$sender->full_name ?? $sender->email, $this->targetModel->getFormattedId()];

        $text = vsprintf(self::TEXT, $args);

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType())
            ->setContext($this->getContextId(), $this->getContextType());

        return json_encode($body->toArray());
    }
}
