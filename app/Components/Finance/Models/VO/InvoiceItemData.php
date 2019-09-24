<?php

namespace App\Components\Finance\Models\VO;

/**
 * Class InvoiceItemData
 *
 * @package App\Components\Finance\Models\VO
 */
class InvoiceItemData extends FinancialEntityItemData
{
    /**
     * @var float
     */
    public $discount = 0;

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param float|null $discount
     */
    public function setDiscount(float $discount = null): void
    {
        if (null === $discount) {
            $discount = .0;
        }

        $this->discount = $discount;
    }
}
