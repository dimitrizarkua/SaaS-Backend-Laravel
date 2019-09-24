<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class SearchGLAccountRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="gl_account_id",
 *          description="GL account identifier.",
 *          type="integer",
 *          nullable=true,
 *          example="1"
 *      ),
 *     @OA\Property(
 *          property="account_type_id",
 *          description="GL account type identifier.",
 *          type="integer",
 *          nullable=true,
 *          example="1"
 *      ),
 *     @OA\Property(
 *          property="locations",
 *          type="array",
 *          @OA\Items(
 *              type="integer",
 *              description="Location identifier.",
 *              example=1
 *          ),
 *     ),
 *      @OA\Property(
 *          property="accounting_organization_id",
 *          description="Accounting organization identifier.",
 *          type="integer",
 *          nullable=true,
 *          example="1"
 *      ),
 *     @OA\Property(
 *          property="is_bank_account",
 *          description="Defines whether an account is bank account.",
 *          type="boolean",
 *          nullable=true,
 *          example=false
 *      ),
 *      @OA\Property(
 *          property="enable_payments_to_account",
 *          description="Defines whether an account is enabled to payments.",
 *          type="boolean",
 *          nullable=true,
 *          example=false
 *      ),
 * )
 */
class SearchGLAccountRequest extends ApiRequest
{
    protected $booleanFields = [
        'is_bank_account',
        'is_debit',
        'enable_payments_to_account',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'gl_account_id'              => 'integer|exists:gl_accounts,id|nullable',
            'accounting_organization_id' => 'integer|exists:accounting_organizations,id|nullable',
            'account_type_id'            => 'integer|exists:account_types,id|nullable',
            'is_bank_account'            => 'boolean|nullable',
            'enable_payments_to_account' => 'boolean|nullable',
            'locations'                  => 'array|nullable',
            'locations.*'                => 'integer|exists:locations,id',
            'is_debit'                   => 'nullable|boolean',
        ];
    }
}
