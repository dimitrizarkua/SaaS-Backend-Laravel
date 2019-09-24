<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateAssessmentReportSectionTextBlockRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"position"},
 *     @OA\Property(
 *         property="position",
 *         description="Position of AR section text block",
 *         type="int",
 *         example=10,
 *     ),
 *     @OA\Property(
 *         property="text",
 *         description="AR section text block text",
 *         type="string",
 *         example="Section text",
 *         nullable=true,
 *     ),
 * )
 */
class CreateAssessmentReportSectionTextBlockRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'position' => 'required|integer',
            'text'     => 'nullable|string',
        ];
    }
}
