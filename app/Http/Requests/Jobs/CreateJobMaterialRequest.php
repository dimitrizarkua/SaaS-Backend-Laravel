<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobMaterialRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={"job_id, material_id, used_at, quantity_used"},
 *     @OA\Property(
 *        property="job_id",
 *        description="Job identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="material_id",
 *        description="Material identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(
 *        property="creator_id",
 *        description="User identifier",
 *        type="integer",
 *        example=1,
 *     ),
 *     @OA\Property(property="used_at", type="string", format="date-time"),
 *     @OA\Property(
 *        property="quantity_used",
 *        description="Units quantity",
 *        type="integer",
 *        example=1
 *     ),
 * )
 */
class CreateJobMaterialRequest extends ApiRequest
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
            'job_id'        => 'required|integer|exists:jobs,id',
            'material_id'   => 'required|integer|exists:materials,id',
            'creator_id'    => 'integer|exists:users,id',
            'used_at'       => 'required|date_format:Y-m-d\TH:i:s\Z',
            'quantity_used' => 'required|integer',
        ];
    }
}
