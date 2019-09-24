<?php

namespace App\Components\Finance\Interfaces;

/**
 * Interface PurchaseOrderTagsServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PurchaseOrderTagsServiceInterface
{
    /**
     * Allows to attach a tag to a purchase order.
     *
     * @param int $purchaseOrderId Purchase order id.
     * @param int $tagId           Tag id.
     *
     * @return void
     */
    public function attachTag(int $purchaseOrderId, int $tagId): void;

    /**
     * Allows to detach a tag from a purchase order.
     *
     * @param int $purchaseOrderId Purchase order id.
     * @param int $tagId           Tag id.
     *
     * @return void
     */
    public function detachTag(int $purchaseOrderId, int $tagId): void;
}
