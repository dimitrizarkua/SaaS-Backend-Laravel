<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Components\UsageAndActuals\Enums\AllowanceTypeChargingIntervals;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class UpdateAllowanceTypeRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
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
class UpdateAllowanceTypeRequest extends ApiRequest
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
            'location_id'              => 'integer|exists:locations,id',
            'name'                     => 'string',
            'charge_rate_per_interval' => 'numeric',
            'charging_interval'        => [
                'string',
                Rule::in(AllowanceTypeChargingIntervals::values()),
            ],
        ];
    }
}
