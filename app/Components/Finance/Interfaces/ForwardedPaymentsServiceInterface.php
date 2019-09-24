<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\InvoicePayment;
use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\ForwardedPaymentData;
use Illuminate\Support\Collection;

/**
 * Class ForwardedPaymentsServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface ForwardedPaymentsServiceInterface
{
    /**
     * Returns list of unforwarded invoice payments.
     *
     * @param int   $locationId  Location identifier.
     * @param array $invoicesIds Specified invoices identifiers.
     *
     * @return \Illuminate\Support\Collection|InvoicePayment[]
     */
    public function getUnforwarded(int $locationId, array $invoicesIds = []): Collection;

    /**
     * Forwards a funds from one GL account to another.
     *
     * @param \App\Components\Finance\Models\VO\ForwardedPaymentData $data
     *
     * @return \App\Components\Finance\Models\Payment
     */
    public function forward(ForwardedPaymentData $data): Payment;
}
