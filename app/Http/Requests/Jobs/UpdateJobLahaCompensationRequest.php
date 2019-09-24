<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobLahaCompensationRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(property="date_started", type="string", format="date-time"),
 *     @OA\Property(
 *         property="days",
 *         description="Number of days",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class UpdateJobLahaCompensationRequest extends ApiRequest
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
            'date_started'         => 'date_format:Y-m-d',
            'days'                 => 'integer',
        ];
    }
}
