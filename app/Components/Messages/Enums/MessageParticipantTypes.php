<?php

namespace App\Components\Messages\Enums;

use vijinho\Enums\Enum;

/**
 * Class MessageParticipantTypes
 *
 * @package App\Components\Messages\Enums
 */
class MessageParticipantTypes extends Enum
{
    const FROM = 'from';
    const TO   = 'to';
    const CC   = 'cc';
    const BCC  = 'bcc';

    protected static $values = [
        'FROM' => self::FROM,
        'TO'   => self::TO,
        'CC'   => self::CC,
        'BCC'  => self::BCC,
    ];

    /**
     * Get values of type recipient.
     *
     * @return array
     */
    public static function getRecipientTypeValues(): array
    {
        return [self::TO, self::CC, self::BCC];
    }
}
