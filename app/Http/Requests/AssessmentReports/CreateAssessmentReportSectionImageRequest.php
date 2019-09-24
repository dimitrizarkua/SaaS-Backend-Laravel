<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateAssessmentReportSectionImageRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"desired_width"},
 *     @OA\Property(
 *         property="photo_id",
 *         description="Photo identifier",
 *         type="int",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="caption",
 *         description="AR section image caption",
 *         type="string",
 *         example="Section caption",
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="desired_width",
 *         description="desired width of an image",
 *         type="int",
 *         example=1024,
 *     ),
 * )
 */
class CreateAssessmentReportSectionImageRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'photo_id'      => 'nullable|integer|exists:photos,id',
            'caption'       => 'nullable|string',
            'desired_width' => 'required|integer',
        ];
    }
}
