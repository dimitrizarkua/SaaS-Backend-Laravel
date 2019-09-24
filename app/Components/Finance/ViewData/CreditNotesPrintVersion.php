<?php

namespace App\Components\Finance\ViewData;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Contracts\ViewDataInterface;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\PurchaseOrder;

/**
 * Class CreditNotesPrintVersion
 *
 * @package App\Components\Finance\ViewData
 *
 * @property PurchaseOrder $entity
 */
class CreditNotesPrintVersion extends FinanceViewData implements ViewDataInterface
{
    /**
     * @param CreditNote $creditNote
     *
     * @return CreditNotesPrintVersion
     */
    public static function make(CreditNote $creditNote): self
    {
        return new static($creditNote);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function toArray(): array
    {
        $status     = $this->entity->isDraft() ? FinancialEntityStatuses::DRAFT : '';
        $itemsBlock = $this->getItemsBlock();

        return [
            //-------------------------------------------
            'creditNoteId'     => $this->entity->id,
            'creditNoteStatus' => $status,
            'recipient'        => $this->getPurchaseOrderToBlock(),
            //-------------------------------------------
            'jobInfo'          => $this->getJobInfo(),
            //-------------------------------------------
            'companyFrom'      => $this->getCompanyFromBlock(),
            //---- Purchase Order Items Table ------------------
            'items'            => $itemsBlock['items'],
            'subTotal'         => $itemsBlock['subTotal'],
            'taxes'            => $itemsBlock['taxes'],
            'total'            => round($itemsBlock['subTotal'] + $itemsBlock['taxes'], 2),
            //-------------------------------------------
            'date'             => $this->entity->date->format('d M Y'),
            'paymentDetails'   => $this->getPaymentDetailsAccountBlock(),
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
