<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateGSCodeRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
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
 *        description="Indicates whether the flag is related to buy operation.",
 *        type="boolean",
 *        example=true
 *     ),
 *     @OA\Property(
 *        property="is_sell",
 *        description="Indicates whether the flag is related to sell operation.",
 *        type="boolean",
 *        example=false,
 *     )
 * )
 */
class UpdateGSCodeRequest extends ApiRequest
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
            'is_buy'      => 'boolean',
            'is_sell'     => 'boolean',
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated(): array
    {
        $validated = parent::validated();

        if (isset($validated['is_buy'])) {
            $validated['is_sell'] = !$validated['is_buy'];

            return $validated;
        }

        if (isset($validated['is_sell'])) {
            $validated['is_buy'] = !$validated['is_sell'];

            return $validated;
        }

        return $validated;
    }
}
