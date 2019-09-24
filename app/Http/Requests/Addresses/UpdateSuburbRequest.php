<?php

namespace App\Http\Requests\Addresses;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateSuburbRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="state_id",
 *         description="State identifier",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="Suburb name",
 *         type="string",
 *         example="Aarons Pass"
 *     ),
 *     @OA\Property(
 *         property="postcode",
 *         description="Suburb postcode",
 *         type="string",
 *         example="2850"
 *     ),
 * )
 *
 * @package App\Http\Requests\Addresses
 */
class UpdateSuburbRequest extends ApiRequest
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
            'state_id' => 'exists:states,id',
            'name'     => 'string',
            'postcode' => 'string',
        ];
    }
}
