<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobLabourRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"labour_type_id", "worker_id", "creator_id", "started_at", "ended_at"},
 *     @OA\Property(
 *        property="job_id",
 *        description="Job identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="labour_type_id",
 *        description="Labour type identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="worker_id",
 *        description="User-worker identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="creator_id",
 *        description="User-creator identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(property="started_at", type="string", format="date-time"),
 *     @OA\Property(property="ended_at", type="string", format="date-time"),
 *     @OA\Property(property="break", type="integer", nullable=true),
 * )
 */
class CreateJobLabourRequest extends ApiRequest
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
            'labour_type_id' => 'required|integer|exists:labour_types,id',
            'worker_id'      => 'required|integer|exists:users,id',
            'creator_id'     => 'required|integer|exists:users,id',
            'started_at'     => 'required|date_format:Y-m-d\TH:i:s\Z',
            'ended_at'       => 'required|date_format:Y-m-d\TH:i:s\Z',
            'break'          => 'integer',
        ];
    }
}
