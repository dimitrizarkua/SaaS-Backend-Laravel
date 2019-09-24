<?php

namespace App\Components\Messages\Enums;

use vijinho\Enums\Enum;

/**
 * Class MessageTypes
 *
 * @package App\Components\Messages\Enums
 */
class MessageTypes extends Enum
{
    const EMAIL = 'email';
    const SMS   = 'sms';

    protected static $values = [
        'EMAIL' => self::EMAIL,
        'SMS'   => self::SMS,
    ];
}
