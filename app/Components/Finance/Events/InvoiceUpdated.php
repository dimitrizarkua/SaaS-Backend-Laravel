<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;

/**
 * Class InvoiceUpdated
 *
 * @package App\Components\Finance\Events
 */
class InvoiceUpdated
{
    /**
     * @var \App\Components\Finance\Models\Invoice
     */
    public $invoice;

    /**
     * InvoiceUpdated constructor.
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
