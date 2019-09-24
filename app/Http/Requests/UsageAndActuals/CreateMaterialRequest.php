<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateMaterialRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
 *          "measure_unit_id",
 *          "default_sell_cost_per_unit",
 *          "default_buy_cost_per_unit",
 *     },
 *     @OA\Property(
 *         property="name",
 *         description="Name of material",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="measure_unit_id",
 *         description="Identifier of measure unit",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="default_sell_cost_per_unit",
 *         description="Default sell cost per unit",
 *         type="number",
 *         example=12.3
 *     ),
 *     @OA\Property(
 *         property="default_buy_cost_per_unit",
 *         description="Default buy cost per unit",
 *         type="number",
 *         example=12.3
 *     ),
 * )
 */
class CreateMaterialRequest extends ApiRequest
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
            'name'                       => 'required|string',
            'measure_unit_id'            => 'required|integer|exists:measure_units,id',
            'default_sell_cost_per_unit' => 'required|numeric',
            'default_buy_cost_per_unit'  => 'required|numeric',
        ];
    }
}
