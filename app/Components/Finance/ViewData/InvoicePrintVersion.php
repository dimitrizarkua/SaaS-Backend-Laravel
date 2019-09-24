<?php

namespace App\Components\Finance\ViewData;

use App\Components\Finance\Enums\TaxRates;
use App\Contracts\ViewDataInterface;
use App\Components\Finance\Models\Invoice;

/**
 * Class InvoicePrintVersion
 *
 * @property Invoice $entity
 *
 * @package App\Components\Finance\ViewData
 */
class InvoicePrintVersion extends FinanceViewData implements ViewDataInterface
{
    /**
     * @inheritdoc
     *
     * @throws \RuntimeException
     */
    public function toArray(): array
    {
        $contactFrom = $this->entity->accountingOrganization->contact;
        $companyFrom = $this->entity->accountingOrganization->contact->company;

        $paymentDetailsAccount = $this->entity->accountingOrganization->paymentDetailsAccount;

        $items = [];
        foreach ($this->entity->items as $item) {
            $amount   = $item->getSubTotal();
            $taxRate  = $item->taxRate->rate;
            $itemData = [
                'description'  => $item->description,
                'qty'          => $item->quantity,
                'item_amount'  => $item->getAmountForOneUnit(),
                'tax_rate'     => 0,
                'total_amount' => $amount,
            ];

            if (TaxRates::GST_ON_INCOME === $item->taxRate->name) {
                $itemData['tax_rate'] = $taxRate * 100 . '%';
            }

            $items[] = $itemData;
        }

        $recipientCompany = $this->entity->recipientContact->company;

        return [
            'invoiceId'      => $this->entity->id,
            'isDraft'        => $this->entity->isDraft(),
            'recipient'      => [
                'name'    => $this->entity->recipient_name,
                'address' => $this->formatAddress($this->entity->recipient_address),
                'abn'     => $recipientCompany ? $this->formatAbn($recipientCompany->abn) : '',
            ],
            'date'           => $this->entity->created_at->format('d M Y'),
            'jobInfo'        => $this->getJobInfo(),
            'companyFrom'    => [
                'name'    => $companyFrom->legal_name,
                'address' => $this->formatAddress($this->entity->accountingOrganization->contact->getMailingAddress()),
                'abn'     => $this->formatAbn($companyFrom->abn),
                'email'   => $contactFrom->email,
                'website' => $this->formatUrl($companyFrom->website),
                'phone'   => $this->formatPhone($contactFrom->business_phone),
            ],
            'items'          => $items,
            'subTotal'       => $this->entity->getTotalAmount(),
            'taxes'          => $this->entity->getTaxesAmount(),
            'totalNet'       => $this->entity->getTotalPaid(),
            'amountDue'      => $this->entity->getAmountDue(),
            'dueDate'        => $this->entity->due_at->format('d M Y'),
            'paymentDetails' => [
                'accountName'   => $paymentDetailsAccount->name,
                'bsb'           => $paymentDetailsAccount->bank_bsb,
                'accountNumber' => $paymentDetailsAccount->bank_account_number,
            ],
        ];
    }
}
