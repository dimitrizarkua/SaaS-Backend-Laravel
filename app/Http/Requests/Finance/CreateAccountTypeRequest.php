<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateAccountTypeRequest
 *
 * @package App\Http\Requests\Finance
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
 *     },
 *     @OA\Property(
 *        property="name",
 *        description="Account type name",
 *        type="string",
 *        example="Tax payable"
 *     ),
 *     @OA\Property(
 *        property="increase_action_is_debit",
 *        type="boolean",
 *     ),
 *     @OA\Property(
 *        property="show_on_pl",
 *        type="boolean",
 *     ),
 *     @OA\Property(
 *        property="show_on_bs",
 *        type="boolean",
 *     ),
 * )
 */
class CreateAccountTypeRequest extends ApiRequest
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
            'name'                     => 'required|string|unique:account_types',
            'increase_action_is_debit' => 'boolean',
            'show_on_pl'               => 'boolean',
            'show_on_bs'               => 'boolean',
        ];
    }
}
