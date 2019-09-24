<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\CreditNote;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class AddApproveRequestsToCreditNote
 *
 * @package App\Components\Finance\Events
 */
class AddApproveRequestsToCreditNote extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'credit_note.approve_requests_send';
    //<name> sent approval requests for credit note #<number>
    public const TEXT = '%s sent an approval requests for credit note %s';

    /**
     * AddApproveRequestsToCreditNote constructor.
     *
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     * @param int|null                                  $senderId
     */
    public function __construct(CreditNote $creditNote, ?int $senderId)
    {
        $this->targetModel = $creditNote;
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
            $sender = $this->getSender();
            $args   = [
                $sender->full_name ?? $sender->email,
                $this->targetModel->getFormattedId(),
            ];
        } else {
            $args = ['System', $this->targetModel->getFormattedId()];
        }

        $text = vsprintf(self::TEXT, $args);

        $body = new BodyData($text, $sender);
        $body->setTarget($this->getTargetId(), $this->getTargetType());

        return json_encode($body->toArray());
    }
}
