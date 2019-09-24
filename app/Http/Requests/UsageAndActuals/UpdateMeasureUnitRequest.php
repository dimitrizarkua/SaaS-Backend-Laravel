<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateMeasureUnitRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "name",
 *          "code",
 *     },
 *     @OA\Property(
 *         property="name",
 *         description="Name of measure unit",
 *         type="string",
 *     ),
 *     @OA\Property(
 *         property="code",
 *         description="Code of measure unit",
 *         type="string",
 *     ),
 * )
 */
class UpdateMeasureUnitRequest extends ApiRequest
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
            'name' => 'string',
            'code' => 'string',
        ];
    }
}
