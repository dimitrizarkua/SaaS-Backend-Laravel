<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateTaxRateRequest
 *
 * @package App\Http\Requests\Finance
 * @OA\Schema(
 *     type="object",
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
class UpdateTaxRateRequest extends ApiRequest
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
            'name' => 'string',
            'rate' => 'numeric|min:0|max:1',
        ];
    }
}
