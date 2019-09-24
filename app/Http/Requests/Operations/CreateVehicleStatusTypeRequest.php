<?php

namespace App\Http\Requests\Operations;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateVehicleStatusTypeRequest
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name","makes_vehicle_unavailable"},
 *     @OA\Property(
 *          property="name",
 *          description="Vehicle status type name",
 *          type="string",
 *          example="Out of Service",
 *     ),
 *     @OA\Property(
 *          property="makes_vehicle_unavailable",
 *          type="boolean",
 *          description="Indicates that a vehicle is unavailable for scheduling",
 *          example=true
 *     )
 * )
 *
 * @package App\Http\Requests\Operations
 */
class CreateVehicleStatusTypeRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'                      => 'required|string|unique:vehicle_status_types',
            'makes_vehicle_unavailable' => 'required|boolean',
        ];
    }
}
