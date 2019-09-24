<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\GLAccount;

/**
 * Interface GLAccountListItemInterface
 * Item of receivable GL Account for creating a payment.
 *
 * @package App\Components\Finance\Interfaces
 */
interface GLAccountListItemInterface
{
    /**
     * GL Account id.
     *
     * @return GLAccount
     */
    public function getGlAccount(): GLAccount;

    /**
     * Amount by which the given account should be increased.
     *
     * @return float
     */
    public function getAmount(): float;
}
