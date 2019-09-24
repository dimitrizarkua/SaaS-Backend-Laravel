<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class SnoozeJobTaskRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"snoozed_until"},
 *     @OA\Property(
 *         property="snoozed_until",
 *         description="Snoozed until",
 *         type="string",
 *         format="date-time",
 *         example="2018-11-10T09:10:11Z"
 *     ),
 * )
 */
class SnoozeJobTaskRequest extends ApiRequest
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
            'snoozed_until' => 'required|date_format:Y-m-d\TH:i:s\Z|after:today',
        ];
    }
}
