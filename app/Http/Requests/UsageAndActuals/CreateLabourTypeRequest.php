<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateLabourTypeRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
 *          "first_tier_hourly_rate",
 *          "second_tier_hourly_rate",
 *          "third_tier_hourly_rate",
 *          "fourth_tier_hourly_rate",
 *     },
 *     @OA\Property(
 *         property="name",
 *         description="Name of material",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="first_tier_hourly_rate",
 *         description="First tier hourly rate",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 *     @OA\Property(
 *         property="second_tier_hourly_rate",
 *         description="Second tier hourly rate",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 *     @OA\Property(
 *         property="third_tier_hourly_rate",
 *         description="Third tier hourly rate",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 *     @OA\Property(
 *         property="fourth_tier_hourly_rate",
 *         description="Fourth tier hourly rate",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 * )
 */
class CreateLabourTypeRequest extends ApiRequest
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
            'name'                    => 'required|string|unique:labour_types',
            'first_tier_hourly_rate'  => 'required|numeric',
            'second_tier_hourly_rate' => 'required|numeric',
            'third_tier_hourly_rate'  => 'required|numeric',
            'fourth_tier_hourly_rate' => 'required|numeric',
        ];
    }
}
