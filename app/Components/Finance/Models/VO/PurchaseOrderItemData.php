<?php

namespace App\Components\Finance\Models\VO;

/**
 * Class PurchaseOrderItemData
 *
 * @package App\Components\Finance\Models\VO
 */
class PurchaseOrderItemData extends FinancialEntityItemData
{
    /**
     * @var float
     */
    public $markup = 0;

    /**
     * @return float
     */
    public function getMarkup(): float
    {
        return $this->markup;
    }

    /**
     * @param float|null $markup
     *
     * @return self
     */
    public function setMarkup(float $markup = null): self
    {
        if (null === $markup) {
            $markup = .0;
        }

        $this->markup = $markup;

        return $this;
    }
}
