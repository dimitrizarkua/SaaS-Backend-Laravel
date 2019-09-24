<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use Illuminate\Queue\SerializesModels;

/**
 * Class PurchaseOrderStatusChanged
 *
 * @package App\Components\Finance\Events
 */
class PurchaseOrderStatusChanged
{
    use SerializesModels;

    /** @var PurchaseOrder */
    public $purchaseOrder;

    /**
     * Create a new PurchaseOrderStatusChanged instance.
     *
     * @param PurchaseOrder $purchaseOrder Purchase order which changed status.
     *
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }
}
