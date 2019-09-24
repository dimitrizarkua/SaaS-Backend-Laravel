<?php

namespace App\Components\Finance\ViewData;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\PurchaseOrder;
use App\Contracts\ViewDataInterface;

/**
 * Class PurchaseOrderPrintVersion
 *
 * @package App\Components\Finance\ViewData
 *
 * @property PurchaseOrder $entity
 */
class PurchaseOrderPrintVersion extends FinanceViewData implements ViewDataInterface
{
    /**
     * @param \App\Components\Finance\Models\PurchaseOrder $purchaseOrder
     *
     * @return \App\Components\Finance\ViewData\PurchaseOrderPrintVersion
     */
    public static function make(PurchaseOrder $purchaseOrder): self
    {
        return new static($purchaseOrder);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function toArray(): array
    {
        //a draft purchase order will have the word “DRAFT” printed
        $purchaseOrderStatus = !$this->entity->isApproved() ? FinancialEntityStatuses::DRAFT : '';
        $itemsBlock          = $this->getItemsBlock();

        return [
            //-------------------------------------------
            'purchaseOrderId'     => $this->entity->id,
            'purchaseOrderStatus' => $purchaseOrderStatus,
            'recipient'           => $this->getPurchaseOrderToBlock(),
            //-------------------------------------------
            'jobInfo'             => $this->getJobInfo(),
            //-------------------------------------------
            'companyFrom'         => $this->getCompanyFromBlock(),
            //---- Purchase Order Items Table ------------------
            'items'               => $itemsBlock['items'],
            'subTotal'            => $itemsBlock['subTotal'],
            'taxes'               => $itemsBlock['taxes'],
            'total'               => round($itemsBlock['subTotal'] + $itemsBlock['taxes'], 2),
            //-------------------------------------------
            'date'                => $this->entity->date->format('d M Y'),
            'paymentDetails'      => $this->getPaymentDetailsAccountBlock(),
        ];
    }

    /**
     * Returns array which contains information about purchase order recipient contact.
     *
     * @return array
     */
    private function getPurchaseOrderToBlock()
    {
        $abn = '';
        if (null !== $this->entity->recipientContact->company) {
            $abn = $this->entity->recipientContact->company->abn;
        }

        return [
            'name'    => $this->entity->recipient_name,
            'address' => $this->formatAddress($this->entity->recipient_address),
            'abn'     => $this->formatAbn($abn),
        ];
    }

    /**
     * Returns array which contains information about accounting organization contact.
     *
     * @return array
     */
    private function getCompanyFromBlock()
    {
        $contactFrom = $this->entity->accountingOrganization->contact;
        $companyFrom = $this->entity->accountingOrganization->contact->company;

        if (null === $companyFrom) {
            $companyFrom = $contactFrom->person;

            return [
                'name'    => $companyFrom->getFullName(),
                'address' => $this->formatAddress(
                    $contactFrom->getMailingAddress()
                ),
                'abn'     => '',
                'email'   => $contactFrom->email,
                'website' => '',
                'phone'   => $this->formatPhone($contactFrom->business_phone),
            ];
        }

        return [
            'name'    => $companyFrom->legal_name,
            'address' => $this->formatAddress(
                $contactFrom->getMailingAddress()
            ),
            'abn'     => $this->formatAbn($companyFrom->abn),
            'email'   => $contactFrom->email,
            'website' => $this->formatUrl($companyFrom->website),
            'phone'   => $this->formatPhone($contactFrom->business_phone),
        ];
    }

    /**
     * Returns array which contains information about payment details.
     *
     * @return array
     */
    private function getPaymentDetailsAccountBlock()
    {
        if (null === $this->entity->accountingOrganization->paymentDetailsAccount) {
            return null;
        }

        $paymentDetailsAccount = $this->entity->accountingOrganization->paymentDetailsAccount;

        return [
            'accountName'   => $paymentDetailsAccount->name,
            'bsb'           => $paymentDetailsAccount->bank_bsb,
            'accountNumber' => $paymentDetailsAccount->bank_account_number,
        ];
    }

    /**
     * Returns array which contains information about purchase order items.
     *
     * @return array
     */
    private function getItemsBlock()
    {
        $result = [
            'items'    => [],
            'subTotal' => 0,
            'taxes'    => 0,
        ];

        foreach ($this->entity->items as $item) {
            $amount             = $item->getSubTotal();
            $itemData           = [
                'description'  => $item->description,
                'qty'          => $item->quantity,
                'unit_cost'    => $item->getAmountForOneUnit(),
                'tax_rate'     => round($item->taxRate->rate * 100, 2),
                'total_amount' => $amount,
            ];
            $result['subTotal'] += $amount;
            $result['taxes']    += round($amount * $item->taxRate->rate, 2);
            $result['items'][]  = $itemData;
        }

        return $result;
    }
}
