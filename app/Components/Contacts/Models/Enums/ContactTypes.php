<?php

namespace App\Components\Contacts\Models\Enums;

use vijinho\Enums\Enum;

/**
 * Class ContactTypes
 *
 * @package App\Components\Contacts\Models\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Contact type",
 *     enum={"person","company"},
 * )
 */
class ContactTypes extends Enum
{
    const PERSON  = 'person';
    const COMPANY = 'company';

    protected static $values = [
        'PERSON'  => self::PERSON,
        'COMPANY' => self::COMPANY,
    ];
}
