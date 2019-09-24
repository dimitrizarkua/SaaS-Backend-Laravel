<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateAssessmentReportSectionCostItemRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"assessment_report_cost_item_id", "position"},
 *     @OA\Property(
 *         property="assessment_report_cost_item_id",
 *         description="Identifier of assessment report cost item",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="position",
 *         description="Position of AR section cost item",
 *         type="int",
 *         example=10,
 *     ),
 * )
 */
class CreateAssessmentReportSectionCostItemRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'assessment_report_cost_item_id' => 'required|integer|exists:assessment_report_cost_items,id',
            'position'                       => 'required|integer',
        ];
    }
}
