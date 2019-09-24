<?php

namespace App\Components\Finance\Models\VO;

/**
 * Class CreatePurchaseOrderData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreatePurchaseOrderData extends CreateFinancialEntityData
{
    /**
     * @var PurchaseOrderItemData[]
     */
    public $items = [];

    /**
     * @var  null|string
     */
    public $reference;
}
