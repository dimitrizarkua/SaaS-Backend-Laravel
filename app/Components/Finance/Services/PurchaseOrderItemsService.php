<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Events\PurchaseOrderItemsUpdated;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\PurchaseOrderItemsServiceInterface;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Exceptions\Api\ValidationException;

/**
 * Class PurchaseOrderItemsService
 *
 * @package App\Components\Finance\Services
 */
class PurchaseOrderItemsService extends PurchaseOrderEntityService implements PurchaseOrderItemsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getPurchaseOrderItem(int $purchaseOrderId, int $purchaseOrderItemId): PurchaseOrderItem
    {
        return PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchaseOrderId)
            ->findOrFail($purchaseOrderItemId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function createPurchaseOrderItem(int $purchaseOrderId, array $attributes): PurchaseOrderItem
    {
        $purchaseOrder = $this->purchaseOrderService()
            ->getEntity($purchaseOrderId);
        if ($purchaseOrder->isApproved()) {
            throw new NotAllowedException('No user can add an item to approved purchase order.');
        }

        try {
            /** @var PurchaseOrderItem $purchaseOrderItem */
            $purchaseOrderItem = $purchaseOrder->items()
                ->create($attributes);
        } catch (\Illuminate\Database\QueryException $e) {
            throw new ValidationException('Validation error has occurred when trying to create a purchase order item.');
        }

        event(new PurchaseOrderItemsUpdated($purchaseOrder));

        return $purchaseOrderItem;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \Throwable
     */
    public function updatePurchaseOrderItem(
        int $purchaseOrderId,
        int $purchaseOrderItemId,
        array $attributes
    ): void {
        $purchaseOrder = $this->purchaseOrderService()
            ->getEntity($purchaseOrderId);
        if ($purchaseOrder->isApproved()) {
            throw new NotAllowedException('Approved purchase order items can\'t be edited.');
        }

        $purchaseOrderItem = $this->getPurchaseOrderItem($purchaseOrderId, $purchaseOrderItemId);

        try {
            $purchaseOrderItem->update($attributes);
        } catch (\Illuminate\Database\QueryException $e) {
            throw new ValidationException('Validation error has occurred when trying to create a purchase order item.');
        }

        event(new PurchaseOrderItemsUpdated($purchaseOrder));
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function deletePurchaseOrderItem(int $purchaseOrderId, int $purchaseOrderItemId): void
    {
        $purchaseOrder = $this->purchaseOrderService()
            ->getEntity($purchaseOrderId);
        if ($purchaseOrder->isApproved()) {
            throw new NotAllowedException('Approved purchase order items can\'t be edited.');
        }

        $purchaseOrderItem = $this->getPurchaseOrderItem($purchaseOrderId, $purchaseOrderItemId);
        $purchaseOrderItem->delete();
        event(new PurchaseOrderItemsUpdated($purchaseOrder));
    }
}
