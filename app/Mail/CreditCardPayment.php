<?php

namespace App\Mail;

use App\Components\Finance\Models\VO\PaymentReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class CreditCardPayment
 *
 * @package App\Mail
 */
class CreditCardPayment extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var PaymentReceipt */
    public $receipt;

    /**
     * @var string
     */
    public $recipientEmail;

    /**
     * Create a new message instance.
     *
     * @param string         $recipientEmail
     * @param PaymentReceipt $receipt
     */
    public function __construct(string $recipientEmail, PaymentReceipt $receipt)
    {
        $this->recipientEmail = $recipientEmail;
        $this->receipt        = $receipt;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->recipientEmail)
            ->subject('Credit Card payment has been processed')
            ->view('finance.credit-card-payment-receipt')
            ->with([
                'receipt' => $this->receipt->toArray(),
            ]);
    }
}
