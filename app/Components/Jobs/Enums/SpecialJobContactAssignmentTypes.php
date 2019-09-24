<?php

namespace App\Components\Messages\Enums;

use vijinho\Enums\Enum;

/**
 * Class SpecialJobContactAssignmentTypes
 *
 * @package App\Components\Messages\Enums
 */
class SpecialJobContactAssignmentTypes extends Enum
{
    const CUSTOMER     = 'Customer';
    const SITE_CONTACT = 'Site Contact';

    protected static $values = [
        'CUSTOMER'     => self::CUSTOMER,
        'SITE_CONTACT' => self::SITE_CONTACT,
    ];
}
