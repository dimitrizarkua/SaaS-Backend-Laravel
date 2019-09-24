<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateAssessmentReportRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"date"},
 *     @OA\Property(
 *         property="heading",
 *         description="Heading of AR",
 *         type="string",
 *         example="Head",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="subheading",
 *         description="Subheading of AR",
 *         type="string",
 *         example="Subhead",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="date",
 *         description="Date",
 *         type="string",
 *         format="date",
 *         example="2018-11-10"
 *     ),
 * )
 */
class CreateAssessmentReportRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'heading'    => 'nullable|string',
            'subheading' => 'nullable|string',
            'date'       => 'required|date',
        ];
    }
}
