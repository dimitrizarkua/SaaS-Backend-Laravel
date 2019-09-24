<?php

namespace App\Components\Finance\Models\VO;

use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\Invoice;
use App\Helpers\Decimal;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class ReceivePaymentData
 *
 * @package App\Components\Finance\Models\VO
 */
class ReceivePaymentData extends CreateInvoicePaymentsData
{
    /**
     * @var int
     */
    public $dst_gl_account_id;

    /**
     * @var int
     */
    public $location_id;

    /**
     * @return array
     * @throws \Throwable
     */
    private function getAmountsAllocation(): array
    {
        $fpAmount = $dpAmount = 0;
        $invoiceIds = collect($this->invoices_list)->pluck('invoice_id')->toArray();
        $invoices = Invoice::with(['payments', 'items'])
            ->whereIn('id', $invoiceIds)
            ->get()
            ->keyBy('id');

        foreach ($this->invoices_list as $paymentInvoiceItem) {
            if (Decimal::gt(
                $paymentInvoiceItem->amount,
                $invoices[$paymentInvoiceItem['invoice_id']]->getAmountDue()
            )) {
                throw new NotAllowedException('Invoice payment value cannot be more balance due value.');
            }

            if ($paymentInvoiceItem->is_fp) {
                $fpAmount += $paymentInvoiceItem->amount;
            } else {
                $dpAmount += $paymentInvoiceItem->amount;
            }
        }

        return ['dpAmount' => $dpAmount, 'fpAmount' => $fpAmount];
    }

    /**
     * @param AccountingOrganization $accountingOrganization Accounting organization.
     * @param float                  $fpAmount               Amount of invoices marked as FP.
     *
     * @return self
     * @throws \Throwable
     */
    private function setTargetGLAccountListItemForFp(
        AccountingOrganization $accountingOrganization,
        float $fpAmount
    ): self {
        try {
            $franchisePaymentAccount = GLAccount::withCode(
                GLAccount::FRANCHISE_PAYMENTS_ACCOUNT_CODE,
                $accountingOrganization->id
            )->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new NotAllowedException(
                'Franchise Payments (Holding) account does not exist.'
            );
        }

        $this->payment_data->payableGLAccountList[] = new GLAccountListItem([
            'glAccount' => $franchisePaymentAccount->id,
            'amount'    => $fpAmount,
        ]);

        return $this;
    }

    /**
     * @param float $dpAmount Amount of invoices marked as DP.
     *
     * @return self
     * @throws \Throwable
     */
    private function setTargetGLAccountListItemForDp(float $dpAmount): self
    {
        $glAccountService = app()->make(GLAccountServiceInterface::class);
        $dstGLAccount     = $glAccountService->getGLAccount($this->dst_gl_account_id);

        if (null === $dstGLAccount->bank_account_name) {
            throw new NotAllowedException('Debit account must be bank account.');
        }

        $dstGLAccountListItem = new GLAccountListItem([
            'glAccount' => $dstGLAccount->id,
            'amount'    => $dpAmount,
        ]);

        if ($dstGLAccount->accountType->increase_action_is_debit) {
            $this->payment_data->receivableGLAccountList[] = $dstGLAccountListItem;
        } else {
            $this->payment_data->payableGLAccountList[] = $dstGLAccountListItem;
        }

        return $this;
    }

    /**
     * @param AccountingOrganization $accountingOrganization Accounting organization.
     * @param float                  $dpAmount               Amount of invoices marked as DP.
     * @param float                  $fpAmount               Amount of invoices marked as FP.
     *
     * @return self
     * @throws \Throwable
     */
    private function setSourceGLAccountListItem(
        AccountingOrganization $accountingOrganization,
        float $dpAmount,
        float $fpAmount
    ): self {
        if (null === $accountingOrganization->receivableAccount) {
            throw new NotAllowedException(
                'There is no receivable account specified for given accounting organization.'
            );
        }

        $this->payment_data->payableGLAccountList[] = new GLAccountListItem([
            'glAccount' => $accountingOrganization->accounts_receivable_account_id,
            'amount'    => $dpAmount + $fpAmount,
        ]);

        return $this;
    }

    /**
     * @param int $accountingOrganizationId Accounting organization identifier.
     *
     * @return self
     */
    private function setAccountingOrganizationId(int $accountingOrganizationId): self
    {
        $this->payment_data->setAccountingOrganizationId($accountingOrganizationId);

        return $this;
    }

    /**
     * @param float $amount Amount of invoices.
     *
     * @return self
     */
    private function setPaymentAmount(float $amount): self
    {
        $this->payment_data->amount = $amount;

        return $this;
    }

    /**
     * @param AccountingOrganization $accountingOrganization Accounting organization.
     *
     * @return self
     * @throws \Throwable
     */
    public function getCreatePaymentData(AccountingOrganization $accountingOrganization): self
    {
        $amountsAllocation = $this->getAmountsAllocation();

        $this->setAccountingOrganizationId($accountingOrganization->id);

        if (Decimal::gt($amountsAllocation['fpAmount'], 0)) {
            $this->setTargetGLAccountListItemForFp($accountingOrganization, $amountsAllocation['fpAmount']);
        }

        if (Decimal::gt($amountsAllocation['dpAmount'], 0)) {
            $this->setTargetGLAccountListItemForDp($amountsAllocation['dpAmount']);
        }

        $this->setSourceGLAccountListItem(
            $accountingOrganization,
            $amountsAllocation['dpAmount'],
            $amountsAllocation['fpAmount']
        );

        $this->setPaymentAmount($amountsAllocation['dpAmount'] + $amountsAllocation['fpAmount']);

        return $this;
    }
}
