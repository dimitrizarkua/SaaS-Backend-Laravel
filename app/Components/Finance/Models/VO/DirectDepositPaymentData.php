<?php

namespace App\Components\Finance\Models\VO;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\Invoice;
use App\Core\JsonModel;
use App\Models\User;

/**
 * Class DirectDepositPaymentData
 *
 * @package App\Components\Finance\Models\VO
 */
class DirectDepositPaymentData extends JsonModel
{
    /**
     * @var float
     */
    public $amount;
    /**
     * @var string
     */
    public $paid_at;
    /**
     * @var integer
     */
    public $gl_account_id;
    /**
     * @var string|null
     */
    public $reference;

    /**
     * @var Invoice
     */
    public $invoice;

    /**
     * @var User
     */
    public $user;

    /**
     * @param Invoice $invoice
     *
     * @return DirectDepositPaymentData
     */
    public function setInvoice(Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * @param User $user
     *
     * @return DirectDepositPaymentData
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @throws \JsonMapper_Exception
     *
     * @return CreateInvoicePaymentsData
     */
    public function getInvoicePaymentsData(): CreateInvoicePaymentsData
    {
        $accountingOrganization = $this->invoice->accountingOrganization;

        $receivableAccount = $accountingOrganization->receivableAccount;
        if (null === $receivableAccount) {
            throw new NotAllowedException('There is no receivable GL Account linked with Accounting Organization');
        }

        $paymentData = new CreatePaymentData([
            'userId'                   => $this->user->id,
            'amount'                   => $this->amount,
            'paidAt'                   => $this->paid_at,
            'reference'                => $this->reference,
            'accountingOrganizationId' => $accountingOrganization->id,
            'payableGLAccountList'     => [
                [
                    'glAccount' => $receivableAccount,
                    'amount'    => $this->amount,
                ],
            ],
            'receivableGLAccountList'  => [
                [
                    'glAccount' => $this->gl_account_id,
                    'amount'    => $this->amount,
                ],
            ],
        ]);

        return (new CreateInvoicePaymentsData())
            ->setPaymentData($paymentData)
            ->addInvoiceItem($this->invoice->id, $this->amount);
    }
}
