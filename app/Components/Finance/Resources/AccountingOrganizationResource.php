<?php

namespace App\Components\Finance\Resources;

use App\Components\Contacts\Resources\ContactResource;
use App\Components\Contacts\Resources\ContactWithAddressResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class AccountingOrganizationResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     allOf={@OA\Schema(ref="#/components/schemas/AccountingOrganization")},
 * )
 * @mixin \App\Components\Finance\Models\AccountingOrganization
 */
class AccountingOrganizationResource extends JsonResource
{

    /**
     * @OA\Property(
     *     property="contact",
     *     ref="#/components/schemas/ContactWithAddressResource"
     * ),
     * @OA\Property(
     *     property="tax_payable_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     * @OA\Property(
     *     property="tax_receivable_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     * @OA\Property(
     *     property="accounts_payable_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     * @OA\Property(
     *     property="accounts_receivable_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     * @OA\Property(
     *     property="payment_details_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource->toArray();

        $result['contact'] = ContactWithAddressResource::make($this->contact);

        if (isset($this->tax_payable_account_id)) {
            $result['tax_payable_account'] = GLAccountResource::make($this->taxPayableAccount);
        }
        if (isset($this->tax_receivable_account_id)) {
            $result['tax_receivable_account'] = GLAccountResource::make($this->taxReceivableAccount);
        }
        if (isset($this->accounts_payable_account_id)) {
            $result['accounts_payable_account'] = GLAccountResource::make($this->payableAccount);
        }
        if (isset($this->accounts_receivable_account_id)) {
            $result['accounts_receivable_account'] = GLAccountResource::make($this->receivableAccount);
        }
        if (isset($this->payment_details_account_id)) {
            $result['payment_details_account'] = GLAccountResource::make($this->paymentDetailsAccount);
        }

        return $result;
    }
}
