<?php

namespace App\Enums;

use vijinho\Enums\Enum;

/**
 * Class ModelChangedEventTypes
 *
 * @package App\Enums
 */
class ModelChangedEventTypes extends Enum
{
    public const CREATED = 'created';
    public const UPDATED = 'updated';
    public const DELETED = 'deleted';

    protected static $values = [
        'CREATED' => self::CREATED,
        'UPDATED' => self::UPDATED,
        'DELETED' => self::DELETED,
    ];
}
