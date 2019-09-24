<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ParseAddressRequest
 *
 * @package App\Http\Requests\Addresses
 *
 * @OA\Schema(
 *     type="object",
 *     required={"address"},
 *     @OA\Property(
 *         property="address",
 *         description="Address to parse",
 *         type="string",
 *         example="143 Mason St, Newport VIC 3015"
 *     ),
 * )
 */
class ParseAddressRequest extends ApiRequest
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
            'address' => 'required|string',
        ];
    }

    /**
     * Returns address string from request.
     *
     * @return string
     */
    public function getAddress(): string
    {
        return $this->input('address');
    }
}
