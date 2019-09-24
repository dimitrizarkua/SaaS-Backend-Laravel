<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;

/**
 * Class NoteAttachedToCreditNote
 *
 * @property CreditNote $targetModel
 * @property Note       $contextModel
 *
 * @package App\Components\Finance\Events
 */
class NoteAttachedToCreditNote extends UserNotificationEvent
{
    public const TYPE = 'credit_note.note_attached';
    //<user_name> added a note to credit note #<id>-location_code
    public const TEXT = '%s added a note to credit note %s';

    /**
     * Create a new event instance.
     *
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     * @param Note                                      $note
     */
    public function __construct(CreditNote $creditNote, Note $note)
    {
        $this->targetModel  = $creditNote;
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

        $args = [
            $sender->full_name ?? $sender->email,
            $this->targetModel->getFormattedId(),
        ];

        $text = vsprintf(self::TEXT, $args);

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType());
        $body->setContext($this->getContextId(), $this->getContextType());

        return json_encode($body->toArray());
    }
}
