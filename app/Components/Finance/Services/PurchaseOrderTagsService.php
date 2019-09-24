<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface;
use App\Components\Finance\Exceptions\NotAllowedException;
use Exception;

/**
 * Class PurchaseOrderTagsService
 *
 * @package App\Components\Finance\Services
 */
class PurchaseOrderTagsService extends PurchaseOrderEntityService implements PurchaseOrderTagsServiceInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Jobs\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function attachTag(int $purchaseOrderId, int $tagId): void
    {
        $purchaseOrder = $this->purchaseOrderService()->getEntity($purchaseOrderId);

        try {
            $purchaseOrder->tags()->attach($tagId);
        } catch (Exception $exception) {
            throw new NotAllowedException('This tag is already assigned to specified purchase order.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function detachTag(int $purchaseOrderId, int $tagId): void
    {
        $purchaseOrder = $this->purchaseOrderService()->getEntity($purchaseOrderId);

        $purchaseOrder->tags()->detach($tagId);
    }
}
