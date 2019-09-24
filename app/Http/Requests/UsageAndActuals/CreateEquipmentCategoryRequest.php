<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Components\UsageAndActuals\Enums\EquipmentCategoryChargingIntervals;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class CreateEquipmentCategoryRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "name",
 *         "is_airmover",
 *         "is_dehum",
 *         "default_buy_cost_per_interval",
 *         "charging_rate_per_interval",
 *         "charging_interval",
 *     },
 *     @OA\Property(
 *         property="name",
 *         description="Name of equipment category",
 *         type="string",
 *         example="Blasting Major Loss Kit",
 *     ),
 *     @OA\Property(
 *         property="is_airmover",
 *         description="Defines whether is it airmover",
 *         type="boolean",
 *         example=true,
 *     ),
 *     @OA\Property(
 *         property="is_dehum",
 *         description="Defines whether is it dehumidifier",
 *         type="boolean",
 *         example=false,
 *     ),
 *     @OA\Property(
 *         property="default_buy_cost_per_interval",
 *         description="Default buy cost per interval",
 *         type="number",
 *         format="float",
 *         example=50.85,
 *     ),
 *     @OA\Property(
 *         property="charging_rate_per_interval",
 *         description="Default charge rate per interval",
 *         type="number",
 *         format="float",
 *         example=50.35,
 *     ),
 *     @OA\Property(
 *         property="charging_interval",
 *         description="Default charging interval",
 *         ref="#/components/schemas/EquipmentCategoryChargingIntervals"
 *     ),
 *     @OA\Property(
 *         property="max_count_to_the_next_interval",
 *         description="Max count to the next interval",
 *         type="integer",
 *         example=4,
 *         default=0,
 *     ),
 *     @OA\Property(
 *         property="charge_rate_per_week",
 *         description="Default charge rate per week",
 *         type="number",
 *         format="float",
 *         example=201.4,
 *         default=0,
 *     ),
 * )
 */
class CreateEquipmentCategoryRequest extends ApiRequest
{
    protected $defaultValues = [
        'max_count_to_the_next_interval' => 0,
        'charge_rate_per_week'           => 0,
    ];

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
            'name'                           => 'required|string|unique:equipment_categories',
            'is_airmover'                    => 'required|boolean',
            'is_dehum'                       => 'required|boolean',
            'default_buy_cost_per_interval'  => 'required|numeric',
            'charging_rate_per_interval'     => 'required|numeric',
            'charging_interval'              => [
                'required',
                'string',
                Rule::in(EquipmentCategoryChargingIntervals::values()),
            ],
            'max_count_to_the_next_interval' => 'integer',
            'charge_rate_per_week'           => 'numeric',
        ];
    }
}
