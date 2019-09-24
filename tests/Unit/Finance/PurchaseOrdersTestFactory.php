<?php

namespace Tests\Unit\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderStatus;

/**
 * Class PurchaseOrdersTestFactory
 *
 * @package Tests\Unit\Finance
 */
class PurchaseOrdersTestFactory
{
    public static function createDraft(array $data = []): PurchaseOrder
    {
        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = factory(PurchaseOrder::class)->create($data);
        factory(PurchaseOrderStatus::class)->create([
            'purchase_order_id' => $purchaseOrder->id,
            'status'            => FinancialEntityStatuses::DRAFT,
        ]);

        return $purchaseOrder;
    }
}
