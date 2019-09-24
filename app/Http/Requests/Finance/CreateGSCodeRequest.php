<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateGSCodeRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
 *          "is_buy",
 *          "is_sell",
 *     },
 *     @OA\Property(
 *        property="name",
 *        description="GS code name",
 *        type="string",
 *        example="L001"
 *     ),
 *     @OA\Property(
 *        property="description",
 *        description="Description GS code",
 *        type="string",
 *        example="description"
 *     ),
 *     @OA\Property(
 *        property="is_buy",
 *        description="Indicates whether the code is related to buy operation.",
 *        type="boolean",
 *        example=true
 *     ),
 *     @OA\Property(
 *        property="is_sell",
 *        description="Indicates whether the code is related to sell operation.",
 *        type="boolean",
 *        example=false,
 *     )
 * )
 */
class CreateGSCodeRequest extends ApiRequest
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
        // todo ask for why we need to store 2 values is_buy, is_sell
        return [
            'name'        => 'required|string|unique:gs_codes',
            'description' => 'string|nullable',
            'is_buy'      => 'required|boolean|different:is_sell',
            'is_sell'     => 'required|boolean|different:is_buy',
        ];
    }
}
