<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Notifications\Events\UserNotificationEvent;
use App\Components\Notifications\Models\VO\BodyData;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Class AddApproveRequestsToPurchaseOrder
 *
 * @package App\Components\Finance\Events
 */
class AddApproveRequestsToPurchaseOrder extends UserNotificationEvent
{
    use SerializesModels;

    public const TYPE = 'purchase_order.approve_requests_send';
    //<name> sent an approval requests for purchase order #<number>
    public const TEXT = '%s sent an approval requests for purchase order %s';

    /**
     * AddApproveRequestsToPurchaseOrder constructor.
     *
     * @param \App\Components\Finance\Models\PurchaseOrder $purchaseOrder
     * @param int|null                                     $senderId
     */
    public function __construct(PurchaseOrder $purchaseOrder, ?int $senderId)
    {
        $this->targetModel = $purchaseOrder;
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
