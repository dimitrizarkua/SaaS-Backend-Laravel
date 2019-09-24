<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateAssessmentReportCostingStageRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         description="Name of costing stage",
 *         type="string",
 *         example="Stage 1",
 *     ),
 *     @OA\Property(
 *         property="position",
 *         description="Position of AR costing stage",
 *         type="int",
 *         example=10,
 *     ),
 * )
 */
class UpdateAssessmentReportCostingStageRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'     => 'string',
            'position' => 'integer',
        ];
    }
}
