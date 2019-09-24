<?php

namespace App\Components\Messages\Models;

use App\Components\Messages\Enums\MessageTypes;

/**
 * Class SMSMessage
 *
 * @package App\Components\Messages\Models
 */
class SMSMessage extends MessageData
{
    /**
     * SMSMessage constructor.
     *
     * @param int|null    $senderId Message sender id (user id).
     * @param array|null  $to       Message recipients.
     * @param null|string $body     Message Body.
     */
    public function __construct(?int $senderId = null, ?array $to = null, ?string $body = null)
    {
        parent::__construct(MessageTypes::SMS, $senderId, $to, $body);
    }
}
