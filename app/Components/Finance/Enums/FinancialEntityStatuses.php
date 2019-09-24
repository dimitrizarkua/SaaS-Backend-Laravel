<?php

namespace App\Components\Finance\Enums;

use OpenApi\Annotations as OA;
use vijinho\Enums\Enum;

/**
 * Class FinancialEntityStatuses
 *
 * @package App\Components\Finance\Enums
 *
 * @OA\Schema(
 *     type="string",
 *     description="Financial entity status",
 *     enum={"draft","approved"},
 * )
 */
class FinancialEntityStatuses extends Enum
{
    public const DRAFT    = 'draft';
    public const APPROVED = 'approved';

    protected static $values = [
        'DRAFT'    => self::DRAFT,
        'APPROVED' => self::APPROVED,
    ];
}
