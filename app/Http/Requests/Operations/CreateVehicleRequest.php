<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateVehicleRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"location_id","type","make","model","registration"},
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
 *          example="Ford"
 *     ),
 *     @OA\Property(
 *          property="model",
 *          type="string",
 *          description="Vehicle model",
 *          example="Transit"
 *     ),
 *     @OA\Property(
 *          property="registration",
 *          type="string",
 *          description="Registration",
 *          example="S550 ABC"
 *     ),
 *     @OA\Property(
 *          property="rent_starts_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true,
 *          description="Rent start time for vehicle.",
 *          example="2018-11-10T09:10:11Z"
 *     ),
 *     @OA\Property(
 *          property="rent_ends_at",
 *          type="string",
 *          format="date-time",
 *          nullable=true,
 *          description="Rent end time for vehicle.",
 *          example="2018-11-10T09:10:11Z"
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class CreateVehicleRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'location_id'    => 'required|integer',
            'type'           => 'required|string',
            'make'           => 'required|string',
            'model'          => 'required|string',
            'registration'   => 'required|string',
            'rent_starts_at' => 'string|nullable|date_format:Y-m-d\TH:i:s\Z',
            'rent_ends_at'   => 'string|nullable|date_format:Y-m-d\TH:i:s\Z|after:rent_starts_at',
        ];
    }
}
