<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateAddressRequest
 *
 * @package App\Http\Requests\Addresses
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="contact_name",
 *         description="Contact name",
 *         type="string",
 *         example="Daniel McKenzie",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="contact_phone",
 *         description="Contact phone number",
 *         type="string",
 *         example="0413456989",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="suburb_id",
 *         description="Suburb id",
 *         type="int",
 *         example="1",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="address_line_1",
 *         description="Address line 1",
 *         type="string",
 *         example="143 Mason St",
 *     ),
 *     @OA\Property(
 *         property="address_line_2",
 *         description="Address line 2",
 *         type="string",
 *         example="143 Mason St",
 *         nullable=true,
 *     ),
 * )
 */
class UpdateAddressRequest extends ApiRequest
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
            'contact_name'   => 'nullable|string',
            'contact_phone'  => 'nullable|string',
            'suburb_id'      => 'nullable|integer|exists:suburbs,id',
            'address_line_1' => 'string',
            'address_line_2' => 'nullable|string',
        ];
    }
}
