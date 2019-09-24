<?php

namespace App\Http\Requests\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportHeadingStyles;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateAssessmentReportSectionRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
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
class UpdateAssessmentReportSectionRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'position'      => 'integer',
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
