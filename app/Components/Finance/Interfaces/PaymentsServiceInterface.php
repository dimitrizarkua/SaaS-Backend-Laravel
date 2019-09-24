<?php

namespace App\Components\Finance\Interfaces;

use App\Components\Finance\Models\Payment;
use App\Components\Finance\Models\VO\CreatePaymentData;
use Illuminate\Database\Eloquent\Builder;

/**
 * Interface PaymentsServiceInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface PaymentsServiceInterface
{
    /**
     * Returns payment by its id.
     *
     * @param int $paymentId Payment id.
     *
     * @return \App\Components\Finance\Models\Payment
     */
    public function getPayment(int $paymentId): Payment;

    /**
     * Allows to find payments relevant for given locations.
     *
     * @param array $locationIds Array of location ids.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function findPaymentsByLocations(array $locationIds): Builder;

    /**
     * Creates direct deposit payment.
     *
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     */
    public function createDirectDepositPayment(CreatePaymentData $paymentData): Payment;

    /**
     * Creates credit note payment.
     *
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     */
    public function createCreditNotePayment(CreatePaymentData $paymentData): Payment;

    /**
     * Creates credit card payment.
     *
     * @param CreatePaymentData $paymentData Payment data.
     *
     * @return \App\Components\Finance\Models\Payment
     */
    public function createCreditCardPayment(CreatePaymentData $paymentData): Payment;
}
