<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;

/**
 * Class InvoiceCreated
 *
 * @package App\Components\Finance\Events
 */
class InvoiceCreated
{
    /**
     * @var \App\Components\Finance\Models\Invoice
     */
    public $invoice;

    /**
     * InvoiceCreated constructor.
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
