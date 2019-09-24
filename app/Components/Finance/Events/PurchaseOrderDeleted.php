<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use Illuminate\Queue\SerializesModels;

/**
 * Class PurchaseOrderDeleted
 *
 * @package App\Components\Finance\Events
 */
class PurchaseOrderDeleted
{
    use SerializesModels;

    /** @var PurchaseOrder */
    public $purchaseOrder;

    /**
     * Create a new PurchaseOrderDeleted instance.
     *
     * @param PurchaseOrder $purchaseOrder Deleted purchase order.
     *
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }
}
