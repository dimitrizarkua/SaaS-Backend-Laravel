<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateVehicleRequest
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *          property="location_id",
 *          type="integer",
 *          description="Location identifier",
 *          example=1
 *     ),
 *     @OA\Property(
 *          property="type",
 *          type="string",
 *          description="Vehicle type",
 *          example="Cargo van"
 *     ),
 *     @OA\Property(
 *          property="make",
 *          type="string",
 *          description="Vehicle make",
 *          example="2015"
 *     ),
 *     @OA\Property(
 *          property="model",
 *          type="string",
 *          description="Vehicle model",
 *          example="Ford transit"
 *     ),
 *     @OA\Property(
 *          property="registration",
 *          type="string",
 *          description="Registration",
 *          example="S550 ABC"
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class UpdateVehicleRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location_id'  => 'integer',
            'type'         => 'string',
            'make'         => 'string',
            'model'        => 'string',
            'registration' => 'string',
        ];
    }
}
