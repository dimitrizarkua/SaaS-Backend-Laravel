<?php

namespace App\Components\Contacts\Events;

use App\Components\Contacts\Models\Contact;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class NoteAttachedToContact
 *
 * @package App\Components\Jobs\Events
 */
class NoteAttachedToContact extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'contact.note_attached';
    //[ You | <name> ] added a note contact #<number>-code
    public const TEXT = '%s added a note to contact %s';

    /**
     * Create a new event instance.
     *
     * @param Contact $contact
     * @param Note    $note
     */
    public function __construct(Contact $contact, Note $note)
    {
        $this->targetModel  = $contact;
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
            $this->targetModel->getFormattedId()
        ];

        $text = vsprintf(self::TEXT, $args);

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType())
            ->setContext($this->getContextId(), $this->getContextType());

        return json_encode($body->toArray());
    }
}
