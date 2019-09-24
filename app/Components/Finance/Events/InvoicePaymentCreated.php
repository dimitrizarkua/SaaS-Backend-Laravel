<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\Invoice;

/**
 * Class InvoicePaymentCreated
 *
 * @package App\Components\Finance\Events
 */
class InvoicePaymentCreated
{
    /**
     * @var \App\Components\Finance\Models\Invoice
     */
    public $invoice;

    /**
     * InvoicePaymentCreated constructor.
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
