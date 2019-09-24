<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobLabourRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(property="started_at_override", type="string", format="date-time"),
 *     @OA\Property(property="ended_at_override", type="string", format="date-time"),
 *     @OA\Property(property="break", type="integer"),
 * )
 */
class UpdateJobLabourRequest extends ApiRequest
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
            'started_at_override' => 'date_format:Y-m-d\TH:i:s\Z',
            'ended_at_override'   => 'date_format:Y-m-d\TH:i:s\Z',
            'break'               => 'integer',
        ];
    }
}
