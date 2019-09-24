<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateEquipmentCategoryRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
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
 * )
 */
class UpdateEquipmentCategoryRequest extends ApiRequest
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
            'name'                          => 'string|unique:equipment_categories',
            'is_airmover'                   => 'boolean',
            'is_dehum'                      => 'boolean',
            'default_buy_cost_per_interval' => 'numeric',
        ];
    }
}
