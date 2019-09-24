<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateStateRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name","country_id","code"},
 *     @OA\Property(
 *         property="country_id",
 *         description="Country id",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="State name",
 *         type="string",
 *         example="New South Wales"
 *     ),
 *     @OA\Property(
 *         property="code",
 *         description="State code",
 *         type="string",
 *         example="NSW"
 *     )
 * )
 *
 * @package App\Http\Requests\Addresses
 */
class CreateStateRequest extends ApiRequest
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
            'country_id' => 'required|exists:countries,id',
            'name'       => 'required|unique:states',
            'code'       => 'required',
        ];
    }
}
