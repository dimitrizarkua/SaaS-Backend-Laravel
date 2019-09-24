<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use Illuminate\Queue\SerializesModels;

/**
 * Class PurchaseOrderCreated
 *
 * @package App\Components\Finance\Events
 */
class PurchaseOrderCreated
{
    use SerializesModels;

    /** @var PurchaseOrder */
    public $purchaseOrder;

    /**
     * Create a new PurchaseOrderCreated instance.
     *
     * @param PurchaseOrder $purchaseOrder Created purchase order.
     *
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }
}
