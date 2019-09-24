<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class NoteAttachedToPurchaseOrder
 *
 * @property PurchaseOrder $targetModel
 * @property Note          $contextModel
 *
 * @package App\Components\Finance\Events
 */
class NoteAttachedToPurchaseOrder extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'purchase_order.note_attached';
    //<user_name> added a note to purchase order #<id>-location_code
    public const TEXT = '%s added a note to purchase order %s';

    /**
     * Create a new event instance.
     *
     * @param PurchaseOrder $purchaseOrder
     * @param Note          $note
     */
    public function __construct(PurchaseOrder $purchaseOrder, Note $note)
    {
        $this->targetModel  = $purchaseOrder;
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
        $body->setTarget($this->getTargetId(), $this->getTargetType())
            ->setContext($this->getContextId(), $this->getContextType());

        return json_encode($body->toArray());
    }
}
