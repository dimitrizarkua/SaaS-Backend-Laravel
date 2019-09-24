<?php

namespace App\Components\Finance\Services;

/**
 * Class PurchaseOrderEntityService
 *
 * @package App\Components\Finance\Services
 */
abstract class PurchaseOrderEntityService
{
    /** @var PurchaseOrdersService */
    private $purchaseOrderService = null;

    /**
     * @return PurchaseOrdersService
     */
    protected function purchaseOrderService(): PurchaseOrdersService
    {
        if (!$this->purchaseOrderService) {
            $this->purchaseOrderService = app()->make(PurchaseOrdersService::class);
        }

        return $this->purchaseOrderService;
    }
}
