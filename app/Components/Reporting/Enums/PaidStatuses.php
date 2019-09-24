<?php

namespace App\Components\Reporting\Enums;

use vijinho\Enums\Enum;
use OpenApi\Annotations as OA;

/**
 * Class PaidStatuses
 *
 * @package App\Components\Reporting\Enums
 * @OA\Schema(
 *     type="string",
 *     enum={"paid","unpaid"},
 * )
 */
class PaidStatuses extends Enum
{
    public const PAID   = 'paid';
    public const UNPAID = 'unpaid';

    protected static $values = [
        'PAID'   => self::PAID,
        'UNPAID' => self::UNPAID,
    ];
}
