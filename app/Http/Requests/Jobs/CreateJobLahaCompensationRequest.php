<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobLahaCompensationRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "job_id",
 *         "user_id",
 *         "creator_id",
 *         "laha_compensation_id",
 *         "date_started",
 *         "days",
 *     },
 *     @OA\Property(
 *         property="job_id",
 *         description="Job identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         description="Payee identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="creator_id",
 *         description="Creator identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="laha_compensation_id",
 *         description="Laha compensation identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(property="date_started", type="string", format="date-time"),
 *     @OA\Property(
 *         property="days",
 *         description="Number of days",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class CreateJobLahaCompensationRequest extends ApiRequest
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
            'job_id'               => 'required|integer|exists:jobs,id',
            'user_id'              => 'required|integer|exists:users,id',
            'creator_id'           => 'required|integer|exists:users,id',
            'laha_compensation_id' => 'required|integer|exists:laha_compensations,id',
            'date_started'         => 'required|date_format:Y-m-d',
            'days'                 => 'required|integer',
        ];
    }
}
