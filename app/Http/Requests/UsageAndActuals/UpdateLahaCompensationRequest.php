<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateLahaCompensationRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="rate_per_day",
 *         description="Rate per day",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 * )
 */
class UpdateLahaCompensationRequest extends ApiRequest
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
            'rate_per_day' => 'numeric',
        ];
    }
}
