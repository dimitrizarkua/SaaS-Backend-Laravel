<?php

namespace App\Http\Requests\UsageAndActuals;

use App\Http\Requests\ApiRequest;

/**
 * Class SearchEquipmentRequest
 *
 * @package App\Http\Requests\UsageAndActuals
 *
 * @OA\Schema(
 *     type="object",
 *     required={"term"},
 *     @OA\Property(
 *         property="term",
 *         description="Search term",
 *         type="string",
 *         example="Absorber"
 *     ),
 *     @OA\Property(
 *         property="job_id",
 *         description="Job identifier",
 *         type="integer",
 *         example=1,
 *     ),
 * )
 */
class SearchEquipmentRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'term'   => 'required|string',
            'job_id' => 'integer|exists:jobs,id',
        ];
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'term' => $this->input('term'),
        ];
    }

    /**
     * @return int|null
     */
    public function getJobId(): ?int
    {
        return $this->input('job_id');
    }
}
