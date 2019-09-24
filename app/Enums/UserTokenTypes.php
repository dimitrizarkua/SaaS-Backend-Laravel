<?php

namespace App\Enums;

use vijinho\Enums\Enum;

/**
 * Class UserTokenTypes
 *
 * @package App\Enums
 */
class UserTokenTypes extends Enum
{
    public const RESET_PASSWORD = 'forgot_password';
    public const INVITE         = 'invite';

    protected static $values = [
        'RESET_PASSWORD' => self::RESET_PASSWORD,
        'INVITE'         => self::INVITE,
    ];
}
