<?php

namespace App\Components\Tags\Enums;

use vijinho\Enums\Enum;

/**
 * Class SpecialTags
 *
 * @package App\Components\Tags\Enums
 */
class SpecialTags extends Enum
{
    const MANAGED_ACCOUNT = 'Managed Account';

    protected static $values = [
        'MANAGED_ACCOUNT' => self::MANAGED_ACCOUNT,
    ];
}
