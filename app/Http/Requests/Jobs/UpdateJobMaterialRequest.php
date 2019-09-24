<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobMaterialRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
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
 *        property="quantity_used_override",
 *        description="Overridden units quantity",
 *        type="integer",
 *        example=1
 *     ),
 * )
 */
class UpdateJobMaterialRequest extends ApiRequest
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
            'job_id'                 => 'integer|exists:jobs,id',
            'material_id'            => 'integer|exists:materials,id',
            'creator_id'             => 'integer|exists:users,id',
            'used_at'                => 'date_format:Y-m-d\TH:i:s\Z',
            'quantity_used_override' => 'integer',
        ];
    }
}
