<?php

namespace App\Components\Messages\Enums;

use vijinho\Enums\Enum;

/**
 * Class MessageStatuses
 *
 * @package App\Components\Messages\Enums
 */
class MessageStatuses extends Enum
{
    const DRAFT                = 'draft';
    const READY_FOR_DELIVERY   = 'ready_for_delivery';
    const DELIVERY_IN_PROGRESS = 'delivery_in_progress';
    const DELIVERED            = 'delivered';
    const DELIVERY_FAILED      = 'delivery_failed';
    const RECEIVED             = 'received';

    protected static $values = [
        'DRAFT'                => self::DRAFT,
        'READY_FOR_DELIVERY'   => self::READY_FOR_DELIVERY,
        'DELIVERY_IN_PROGRESS' => self::DELIVERY_IN_PROGRESS,
        'DELIVERED'            => self::DELIVERED,
        'DELIVERY_FAILED'      => self::DELIVERY_FAILED,
        'RECEIVED'             => self::RECEIVED,
    ];
}
