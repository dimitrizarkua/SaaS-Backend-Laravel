<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;

/**
 * Class InvoiceItemsUpdated
 * Should be fired when invoice item has been added/deleted/updated.
 *
 * @package App\Components\Finance\Events
 */
class InvoiceItemsUpdated
{
    /**
     * @var \App\Components\Finance\Models\Invoice
     */
    public $invoice;

    /**
     * InvoiceItemsUpdated constructor.
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
