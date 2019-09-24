<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;

/**
 * Class NoteAttachedToInvoice
 *
 * @property Invoice $targetModel
 * @property Note    $contextModel
 *
 * @package App\Components\Finance\Events
 */
class NoteAttachedToInvoice extends UserNotificationEvent
{
    public const TYPE = 'invoice.note_attached';
    //<user_name> added a note to invoice #<id>-location_code
    public const TEXT = '%s added a note to invoice %s';

    /**
     * Create a new event instance.
     *
     * @param Invoice $invoice
     * @param Note    $note
     */
    public function __construct(Invoice $invoice, Note $note)
    {
        $this->targetModel  = $invoice;
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
