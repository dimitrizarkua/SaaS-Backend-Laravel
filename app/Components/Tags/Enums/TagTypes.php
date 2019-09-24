<?php

namespace App\Components\Tags\Enums;

use vijinho\Enums\Enum;

/**
 * Class TagTypes
 *
 * @package App\Components\Tags\Enums
 */
class TagTypes extends Enum
{
    const JOB            = 'job';
    const CREDIT_NOTE    = 'credit_note';
    const PURCHASE_ORDER = 'purchase_order';
    const INVOICE        = 'invoice';
    const CONTACT        = 'contact';

    protected static $values = [
        'JOB'            => self::JOB,
        'CREDIT_NOTE'    => self::CREDIT_NOTE,
        'PURCHASE_ORDER' => self::PURCHASE_ORDER,
        'INVOICE'        => self::INVOICE,
        'CONTACT'        => self::CONTACT,
    ];
}
