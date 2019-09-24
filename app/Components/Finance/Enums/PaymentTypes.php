<?php

namespace App\Components\Finance\Enums;

use vijinho\Enums\Enum;
use OpenApi\Annotations as OA;

/**
 * Class PaymentTypes
 *
 * @package App\Components\Finance\Enums
 * @OA\Schema(
 *     type="string",
 *     description="Payment type",
 *     enum={"credit_card","direct_deposit","credit_note"},
 * )
 */
class PaymentTypes extends Enum
{
    public const CREDIT_CARD    = 'credit_card';
    public const DIRECT_DEPOSIT = 'direct_deposit';
    public const CREDIT_NOTE    = 'credit_note';
    public const FORWARDED      = 'forwarded';

    protected static $values = [
        'CREDIT_CARD'    => self::CREDIT_CARD,
        'DIRECT_DEPOSIT' => self::DIRECT_DEPOSIT,
        'CREDIT_NOTE'    => self::CREDIT_NOTE,
        'FORWARDED'      => self::FORWARDED,
    ];
}
