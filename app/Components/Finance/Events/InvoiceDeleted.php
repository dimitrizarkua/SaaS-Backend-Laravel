<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;

/**
 * Class InvoiceDeleted
 *
 * @package App\Components\Finance\Events
 */
class InvoiceDeleted
{
    /**
     * @var \App\Components\Finance\Models\Invoice
     */
    public $invoice;

    /**
     * InvoiceDeleted constructor.
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
