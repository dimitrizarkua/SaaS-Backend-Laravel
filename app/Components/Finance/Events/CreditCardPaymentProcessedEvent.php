<?php

namespace App\Components\Finance\Events;

use App\Components\Finance\Models\VO\PaymentReceipt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditCardPaymentProcessedEvent
 *
 * @package App\Components\Finance\Events
 */
class CreditCardPaymentProcessedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var PaymentReceipt */
    public $receipt;

    /** @var string */
    public $recipientEmail;

    /**
     * CreditCardPaymentProcessedEvent constructor.
     *
     * @param string                                           $recipientEmail
     * @param \App\Components\Finance\Models\VO\PaymentReceipt $receipt
     */
    public function __construct(string $recipientEmail, PaymentReceipt $receipt)
    {
        $this->recipientEmail = $recipientEmail;
        $this->receipt        = $receipt;
    }
}
