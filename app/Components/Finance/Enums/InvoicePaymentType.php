<?php

namespace App\Components\Finance\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class InvoicePaymentType
 *
 * @package App\Components\Finance\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Invoice payment type",
 *     enum={"forwarded", "direct"},
 * )
 */
class InvoicePaymentType extends Enum
{
    public const FORWARDED = 'forwarded';
    public const DIRECT    = 'direct';

    protected static $values = [
        'FORWARDED' => self::FORWARDED,
        'DIRECT'    => self::DIRECT,
    ];
}
