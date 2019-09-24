<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\PurchaseOrder;
use Illuminate\Queue\SerializesModels;

/**
 * Class PurchaseOrderUpdated
 *
 * @package App\Components\Finance\Events
 */
class PurchaseOrderUpdated
{
    use SerializesModels;

    /** @var PurchaseOrder */
    public $purchaseOrder;

    /** @var int */
    public $locationId;

    /** @var bool */
    public $generatePDF;

    /**
     * Create a new PurchaseOrderUpdated instance.
     *
     * @param PurchaseOrder $purchaseOrder Updated purchase order.
     * @param int|null      $locationId    If given than recalculation of counters will be done for that location too.
     * @param bool          $generatePDF   If given than PDF will be generated.
     */
    public function __construct(PurchaseOrder $purchaseOrder, ?int $locationId = null, bool $generatePDF = true)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->locationId    = $locationId;
        $this->generatePDF   = $generatePDF;
    }
}
