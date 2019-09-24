<?php

namespace App\Components\Contacts\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class ContactCategoryTypes
 *
 * @package App\Components\Contacts\Enums
 * @OA\Schema(
 *     type="string",
 *     description="Contact category type",
 *     enum={"customer","supplier","insurer","broker","loss_adjustor","company_location"},
 * )
 */
class ContactCategoryTypes extends Enum
{
    public const CUSTOMER         = 'customer';
    public const SUPPLIER         = 'supplier';
    public const INSURER          = 'insurer';
    public const BROKER           = 'broker';
    public const LOSS_ADJUSTOR    = 'loss_adjustor';
    public const COMPANY_LOCATION = 'company_location';

    protected static $values = [
        'CUSTOMER'         => self::CUSTOMER,
        'SUPPLIER'         => self::SUPPLIER,
        'INSURER'          => self::INSURER,
        'BROKER'           => self::BROKER,
        'LOSS_ADJUSTOR'    => self::LOSS_ADJUSTOR,
        'COMPANY_LOCATION' => self::COMPANY_LOCATION,
    ];
}
