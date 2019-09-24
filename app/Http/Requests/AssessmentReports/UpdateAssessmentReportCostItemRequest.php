<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class UpdateAssessmentReportCostItemRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="assessment_report_costing_stage_id",
 *         description="Identifier of AR costing stage",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="gs_code_id",
 *         description="Identifier of GS code",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="position",
 *         description="AR cost item position",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="AR cost item text",
 *         type="string",
 *         example="Cost item description",
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         description="Used quantity",
 *         type="integer",
 *         example=2,
 *     ),
 *     @OA\Property(
 *         property="unit_cost",
 *         description="Cost per unit",
 *         type="number",
 *         format="float",
 *         example=500.00,
 *     ),
 *     @OA\Property(
 *         property="discount",
 *         description="Discount in percent",
 *         type="number",
 *         format="float",
 *         example=25.00,
 *     ),
 *     @OA\Property(
 *         property="markup",
 *         description="Markup in percent",
 *         type="number",
 *         format="float",
 *         example=30.00,
 *     ),
 *     @OA\Property(
 *         property="tax_rate_id",
 *         description="Identifier of tax rate",
 *         type="integer",
 *         example=1,
 *     ),
 * )
 */
class UpdateAssessmentReportCostItemRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'assessment_report_costing_stage_id' => 'integer|exists:assessment_report_costing_stages,id',
            'gs_code_id'                         => 'integer|exists:gs_codes,id',
            'position'                           => 'integer',
            'description'                        => 'string',
            'quantity'                           => 'integer|min:1',
            'unit_cost'                          => 'numeric|min:0',
            'discount'                           => 'numeric|min:0|max:100',
            'markup'                             => 'numeric|min:0',
            'tax_rate_id'                        => 'integer|exists:tax_rates,id',
        ];
    }
}
