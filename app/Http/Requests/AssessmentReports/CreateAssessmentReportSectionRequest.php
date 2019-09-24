<?php

namespace App\Http\Requests\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportHeadingStyles;
use App\Components\AssessmentReports\Enums\AssessmentReportSectionTypes;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class CreateAssessmentReportSectionRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"type", "position"},
 *     @OA\Property(
 *         property="type",
 *         description="Type of AR section",
 *         ref="#/components/schemas/AssessmentReportSectionTypes"
 *     ),
 *     @OA\Property(
 *         property="position",
 *         description="Position of AR section",
 *         type="int",
 *         example=10,
 *     ),
 *     @OA\Property(
 *         property="heading",
 *         description="AR section heading",
 *         type="string",
 *         example="Section head",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="heading_style",
 *         description="AR section heading style",
 *         ref="#/components/schemas/AssessmentReportStatuses",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="heading_color",
 *         description="AR section heading color",
 *         type="int",
 *         example=16777215,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="text",
 *         description="AR section text",
 *         type="string",
 *         example="Section text",
 *         nullable=true,
 *     ),
 * )
 */
class CreateAssessmentReportSectionRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type'          => [
                'required',
                'string',
                Rule::in(AssessmentReportSectionTypes::values()),
            ],
            'position'      => 'required|integer',
            'heading'       => 'nullable|string',
            'heading_style' => [
                'nullable',
                'string',
                Rule::in(AssessmentReportHeadingStyles::values()),
            ],
            'heading_color' => 'nullable|integer|max:16777215',
            'text'          => 'nullable|string',
        ];
    }
}
