<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;

/**
 * Class CreateInvoicePaymentsData
 *
 * @package App\Components\Finance\Models\VO
 */
class CreateInvoicePaymentsData extends JsonModel
{
    /**
     * @var \App\Components\Finance\Models\VO\CreatePaymentData
     */
    public $payment_data;

    /**
     * @var PaymentInvoiceItem[]
     */
    public $invoices_list = [];

    /**
     * Returns total amount of the items in the invoice.
     *
     * @return float
     */
    public function getItemsAmount(): float
    {
        return collect($this->invoices_list)->sum('amount');
    }

    /**
     * @return \App\Components\Finance\Models\VO\CreatePaymentData
     */
    public function getPaymentData(): CreatePaymentData
    {
        return $this->payment_data;
    }

    /**
     * @param \App\Components\Finance\Models\VO\CreatePaymentData $paymentData
     *
     * @return self
     */
    public function setPaymentData(CreatePaymentData $paymentData): self
    {
        $this->payment_data = $paymentData;

        return $this;
    }

    /**
     * @return PaymentInvoiceItem[]
     */
    public function getInvoicesList(): array
    {
        return $this->invoices_list;
    }

    /**
     * Creates new PaymentInvoiceItem and adds it to invoices_list.
     *
     * @param int   $invoiceId Invoice id.
     * @param float $amount    Amount.
     *
     * @return CreateInvoicePaymentsData
     * @throws \JsonMapper_Exception
     */
    public function addInvoiceItem(int $invoiceId, float $amount): self
    {
        $this->invoices_list[] = new PaymentInvoiceItem([
            'invoice_id' => $invoiceId,
            'amount'     => $amount,
        ]);

        return $this;
    }
}
