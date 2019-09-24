<?php

namespace App\Components\Finance\Enums;

use vijinho\Enums\Enum;

/**
 * Class TaxRates
 *
 * @package App\Components\Finance\Enums
 */
class TaxRates extends Enum
{
    public const GST_ON_INCOME     = 'GST on Income';
    public const GST_ON_EXPENSES   = 'GST on Expenses';
    public const GST_FREE_INCOME   = 'GST Free Income';
    public const GST_FREE_EXPENSES = 'GST Free Expenses';
    public const BAS_EXCLUDED      = 'BAS Excluded';

    protected static $values = [
        'GST_ON_INCOME'     => self::GST_ON_INCOME,
        'GST_ON_EXPENSES'   => self::GST_ON_EXPENSES,
        'GST_FREE_INCOME'   => self::GST_FREE_INCOME,
        'GST_FREE_EXPENSES' => self::GST_FREE_EXPENSES,
        'BAS_EXCLUDED'      => self::BAS_EXCLUDED,
    ];
}
