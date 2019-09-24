<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateGLAccountRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *        property="account_type_id",
 *        description="Identifier of account type",
 *        type="integer",
 *        example="1"
 *     ),
 *     @OA\Property(
 *        property="tax_rate_id",
 *        description="Identifier of Tax Rate",
 *        type="integer",
 *        example="1"
 *     ),
 *     @OA\Property(
 *        property="code",
 *        description="Account code",
 *        type="string",
 *        example="612",
 *        nullable=true
 *     ),
 *     @OA\Property(
 *        property="name",
 *        description="Account name",
 *        type="string",
 *        example="Tax payable"
 *     ),
 *     @OA\Property(
 *        property="bank_account_name",
 *        description="Bank account name",
 *        type="string",
 *        example="Account name",
 *     ),
 *     @OA\Property(
 *        property="description",
 *        description="Account description",
 *        type="string",
 *        example="Some description"
 *     ),
 *     @OA\Property(
 *        property="bank_account_number",
 *        description="Bank account number",
 *        type="string",
 *        example="03-678",
 *     ),
 *     @OA\Property(
 *        property="bank_bsb",
 *        description="Bank BSB number",
 *        type="string",
 *        example="03-678",
 *     ),
 *     @OA\Property(
 *        property="status",
 *        description="Account status",
 *        type="string",
 *        example="some status",
 *     ),
 *     @OA\Property(
 *        property="is_active",
 *        description="Shows whether is this account active",
 *        type="boolean",
 *     ),
 * )
 */
class UpdateGLAccountRequest extends ApiRequest
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
        return [
            'account_type_id'     => 'exists:account_types,id',
            'tax_rate_id'         => 'exists:tax_rates,id',
            'code'                => 'nullable|string',
            'name'                => 'string',
            'description'         => 'string|nullable',
            'bank_account_name'   => 'string|nullable',
            'bank_account_number' => 'string|nullable',
            'bank_bsb'            => 'string|nullable',
            'status'              => 'string',
            'is_active'           => 'boolean',
        ];
    }
}
