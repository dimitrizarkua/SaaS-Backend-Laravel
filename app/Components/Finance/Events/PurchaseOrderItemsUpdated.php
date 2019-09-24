<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use Illuminate\Queue\SerializesModels;

/**
 * Class PurchaseOrderItemsUpdated
 *
 * @package App\Components\Finance\Events
 */
class PurchaseOrderItemsUpdated
{
    use SerializesModels;

    /** @var PurchaseOrder */
    public $purchaseOrder;

    /**
     * Create a new PurchaseOrderItemsUpdated instance.
     *
     * @param PurchaseOrder $purchaseOrder Purchase order for which item was created/updated/deleted.
     *
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }
}
