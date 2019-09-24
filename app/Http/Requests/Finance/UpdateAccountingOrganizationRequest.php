<?php

namespace App\Http\Requests\Finance;

use App\Components\Contacts\Enums\ContactCategoryTypes;
use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToAccountingOrganization;
use App\Rules\ContactCategory;
use OpenApi\Annotations as OA;

/**
 * Class UpdateAccountingOrganizationRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="contact_id",
 *         description="Identifier of contact for which account is being creating",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="tax_payable_account_id",
 *         description="Identifier of Tax Payable GL Account",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="tax_receivable_account_id",
 *         description="Identifier of Tax Receivable GL Account",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="accounts_payable_account_id",
 *         description="Identifier of Accounts Payable GL Account",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="accounts_receivable_account_id",
 *         description="Identifier of Accounts Receivable GL Account",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="payment_details_account_id",
 *         description="Identifier of Payment Details GL Account",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="cc_payments_api_key",
 *         description="Payments API key",
 *         type="string",
 *         example="API KEY"
 *     ),
 * )
 */
class UpdateAccountingOrganizationRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        $glAccountValidationRuleSet = [
            'integer',
            'exists:gl_accounts,id',
            new BelongsToAccountingOrganization($this->getAccountingOrganizationId()),
        ];

        return [
            'contact_id'                     => [
                'integer',
                new ContactCategory(ContactCategoryTypes::COMPANY_LOCATION),
            ],
            'tax_payable_account_id'         => $glAccountValidationRuleSet,
            'tax_receivable_account_id'      => $glAccountValidationRuleSet,
            'accounts_payable_account_id'    => $glAccountValidationRuleSet,
            'accounts_receivable_account_id' => $glAccountValidationRuleSet,
            'payment_details_account_id'     => $glAccountValidationRuleSet,
            'cc_payments_api_key'            => 'string',
        ];
    }

    /**
     * Returns accounting organization from path params.
     *
     * @return mixed
     */
    public function getAccountingOrganizationId()
    {
        return $this->route('accounting_organization')->id;
    }
}
