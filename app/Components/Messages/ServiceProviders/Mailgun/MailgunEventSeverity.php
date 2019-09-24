<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use vijinho\Enums\Enum;

/**
 * Class MailgunEventSeverity
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunEventSeverity extends Enum
{
    const TEMPORARY = 'temporary';
    const PERMANENT = 'permanent';

    protected static $values = [
        'TEMPORARY' => self::TEMPORARY,
        'PERMANENT' => self::PERMANENT,
    ];
}
