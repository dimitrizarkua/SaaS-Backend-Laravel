<?php

namespace App\Components\Messages\Models;

use App\Components\Messages\Enums\MessageTypes;

/**
 * Class EmailMessage
 *
 * @package App\Components\Messages\Models
 */
class EmailMessage extends MessageData
{
    /**
     * EmailMessage constructor.
     *
     * @param int|null    $senderId Message sender id (user id).
     * @param array|null  $to       Message recipients.
     * @param null|string $subject  Message subject.
     * @param null|string $body     Message Body.
     */
    public function __construct(?int $senderId = null, ?array $to = null, ?string $subject = null, ?string $body = null)
    {
        parent::__construct(MessageTypes::EMAIL, $senderId, $to, $body);
        $this->setSubject($subject);
    }
}
