<?php

namespace App\Components\Contacts\Models\Enums;

use vijinho\Enums\Enum;

/**
 * Class ContactStatuses
 *
 * @package App\Components\Contacts\Models\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Contact status",
 *     example="lead",
 *     enum={"lead","active","qualified","customer","inactive"},
 * )
 */
class ContactStatuses extends Enum
{
    const LEAD      = 'lead';
    const ACTIVE    = 'active';
    const QUALIFIED = 'qualified';
    const CUSTOMER  = 'customer';
    const INACTIVE  = 'inactive';

    protected static $values = [
        'LEAD'      => self::LEAD,
        'ACTIVE'    => self::ACTIVE,
        'QUALIFIED' => self::QUALIFIED,
        'CUSTOMER'  => self::CUSTOMER,
        'INACTIVE'  => self::INACTIVE,
    ];
}
