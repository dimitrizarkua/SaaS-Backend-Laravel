<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\PurchaseOrderItem;

/**
 * Interface PurchaseOrderItemsServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderItemsServiceInterface
{
    /**
     * Returns purchase order item model of specified purchase order.
     *
     * @param int $purchaseOrderId     Purchase order id.
     * @param int $purchaseOrderItemId Purchase order item id.
     *
     * @return \App\Components\Finance\Models\PurchaseOrderItem
     */
    public function getPurchaseOrderItem(int $purchaseOrderId, int $purchaseOrderItemId): PurchaseOrderItem;

    /**
     * Creates purchase order item for specified purchase order and return its model.
     *
     * @param int   $purchaseOrderId Purchase order id.
     * @param array $attributes      Purchase order item fields.
     *
     * @return \App\Components\Finance\Models\PurchaseOrderItem
     */
    public function createPurchaseOrderItem(int $purchaseOrderId, array $attributes): PurchaseOrderItem;

    /**
     * Updates purchase order item for specified purchase order and return its model.
     *
     * @param int   $purchaseOrderId     Purchase order id.
     * @param int   $purchaseOrderItemId Purchase order item id.
     * @param array $attributes          Purchase order item fields.
     *
     * @return void
     */
    public function updatePurchaseOrderItem(
        int $purchaseOrderId,
        int $purchaseOrderItemId,
        array $attributes
    ): void;

    /**
     * Removes purchase order item for specified purchase order.
     *
     * @param int $purchaseOrderId     Purchase order id.
     * @param int $purchaseOrderItemId Purchase order item id.
     *
     * @return void
     */
    public function deletePurchaseOrderItem(int $purchaseOrderId, int $purchaseOrderItemId): void;
}
