<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobRoomRequest
 *
 * @package App\Http\Requests\Jobs
 *
 * @OA\Schema(
 *     type="object",
 *     required={"name"},
 *     @OA\Property(
 *         property="flooring_type_id",
 *         description="Id of flooring type.",
 *         type="int",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="name",
 *         description="Name of room",
 *         type="string",
 *         example="Hall",
 *     ),
 *     @OA\Property(
 *         property="total_sqm",
 *         description="Job room total area in square meters",
 *         type="number",
 *         format="float",
 *         example=30,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="affected_sqm",
 *         description="Job room affected area in square meters",
 *         type="number",
 *         format="float",
 *         example=20,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="non_restorable_sqm",
 *         description="Job room non-restorable area in square meters",
 *         type="number",
 *         format="float",
 *         example=5,
 *         nullable=true,
 *     ),
 * )
 */
class CreateJobRoomRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'flooring_type_id'   => 'nullable|integer|exists:flooring_types,id',
            'name'               => 'required|string',
            'total_sqm'          => 'nullable|numeric|min:0',
            'affected_sqm'       => 'nullable|numeric|min:0',
            'non_restorable_sqm' => 'nullable|numeric|min:0',
        ];
    }
}
