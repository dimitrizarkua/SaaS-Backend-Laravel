<?php

namespace App\Components\Finance\Enums;

use vijinho\Enums\Enum;

/**
 * Class AccountTypeGroups
 *
 * @OA\Schema(
 *     type="string",
 *     description="Account type groups",
 *     enum={"Revenues", "Expenses", "Assets", "Liabilities", "Equities"},
 * )
 *
 * @package App\Components\Finance\Enums
 */
class AccountTypeGroups extends Enum
{
    public const REVENUE   = 'Revenues';
    public const EXPENSE   = 'Expenses';
    public const ASSET     = 'Assets';
    public const LIABILITY = 'Liabilities';
    public const EQUITY    = 'Equities';

    protected static $values = [
        'REVENUE'   => self::REVENUE,
        'EXPENSE'   => self::EXPENSE,
        'ASSET'     => self::ASSET,
        'LIABILITY' => self::LIABILITY,
        'EQUITY'    => self::EQUITY,
    ];
}
