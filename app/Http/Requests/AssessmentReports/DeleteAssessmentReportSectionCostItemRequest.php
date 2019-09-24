<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class DeleteAssessmentReportSectionCostItemRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"assessment_report_cost_item_id"},
 *     @OA\Property(
 *         property="assessment_report_cost_item_id",
 *         description="Identifier of assessment report cost item",
 *         type="integer",
 *         example=1,
 *     ),
 * )
 */
class DeleteAssessmentReportSectionCostItemRequest extends ApiRequest
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
        ];
    }

    /**
     * Returns assessment report cost item identifier.
     *
     * @return int
     */
    public function getCostItemId()
    {
        return $this->get('assessment_report_cost_item_id');
    }
}
