<?php

namespace App\Components\Messages\ServiceProviders\Mailgun;

use vijinho\Enums\Enum;

/**
 * Class MailgunEventTypes
 *
 * @package App\Components\Messages\ServiceProviders\Mailgun
 */
class MailgunEventTypes extends Enum
{
    const ACCEPTED     = 'accepted';
    const DELIVERED    = 'delivered';
    const FAILED       = 'failed';
    const OPENED       = 'opened';
    const CLICKED      = 'clicked';
    const UNSUBSCRIBED = 'unsubscribed';
    const COMPLAINED   = 'complained';
    const STORED       = 'stored';
    const REJECTED     = 'rejected';

    protected static $values = [
        'ACCEPTED'     => self::ACCEPTED,
        'DELIVERED'    => self::DELIVERED,
        'FAILED'       => self::FAILED,
        'OPENED'       => self::OPENED,
        'CLICKED'      => self::CLICKED,
        'UNSUBSCRIBED' => self::UNSUBSCRIBED,
        'COMPLAINED'   => self::COMPLAINED,
        'STORED'       => self::STORED,
        'REJECTED'     => self::REJECTED,
    ];
}
