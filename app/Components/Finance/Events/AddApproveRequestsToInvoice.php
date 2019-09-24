<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class AddApproveRequestsToInvoice
 *
 * @package App\Components\Finance\Events
 */
class AddApproveRequestsToInvoice extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'invoice.approve_requests_send';
    //<name> sent an approval requests for invoice #<number>
    public const TEXT = '%s sent an approval requests for invoice %s';

    /**
     * AddApproveRequestsToInvoice constructor.
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     * @param int|null                               $senderId
     */
    public function __construct(Invoice $invoice, ?int $senderId)
    {
        $this->targetModel = $invoice;
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
