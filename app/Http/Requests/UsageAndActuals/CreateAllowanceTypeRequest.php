<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Components\UsageAndActuals\Enums\AllowanceTypeChargingIntervals;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class CreateAllowanceTypeRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "location_id",
 *          "name",
 *          "charge_rate_per_interval",
 *          "charging_interval",
 *     },
 *     @OA\Property(
 *         property="location_id",
 *         description="Location identifier",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="Name of allowance type",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="charge_rate_per_interval",
 *         description="Charge rate per interval",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 *     @OA\Property(
 *         property="charging_interval",
 *         description="Charging interval",
 *         ref="#/components/schemas/AllowanceTypeChargingIntervals"
 *     ),
 * )
 */
class CreateAllowanceTypeRequest extends ApiRequest
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
            'location_id'              => 'required|integer|exists:locations,id',
            'name'                     => 'required|string',
            'charge_rate_per_interval' => 'required|numeric',
            'charging_interval'        => [
                'required',
                'string',
                Rule::in(AllowanceTypeChargingIntervals::values()),
            ],
        ];
    }
}
