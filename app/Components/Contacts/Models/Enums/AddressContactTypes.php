<?php

namespace App\Components\Contacts\Models\Enums;

use vijinho\Enums\Enum;

/**
 * Class AddressContactTypes
 *
 * @package App\Components\Contacts\Models\Enums
 */
class AddressContactTypes extends Enum
{
    const MAILING = 'mailing';
    const STREET  = 'street';

    protected static $values = [
        'MAILING' => self::MAILING,
        'STREET'  => self::STREET,
    ];
}
