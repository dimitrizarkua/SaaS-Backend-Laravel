<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateTaxRateRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","name","rate"},
 *     @OA\Property(
 *        property="name",
 *        description="Name",
 *        type="string",
 *        example="GST on Income"
 *     ),
 *     @OA\Property(
 *        property="rate",
 *        type="number",
 *        example="0.1"
 *     )
 * )
 */
class CreateTaxRateRequest extends ApiRequest
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
            'name' => 'required|string|unique:tax_rates',
            'rate' => 'required|numeric|min:0|max:1',
        ];
    }
}
