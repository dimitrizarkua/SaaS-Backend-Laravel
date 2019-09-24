<?php

namespace App\Http\Requests\AssessmentReports;

use App\Http\Requests\ApiRequest;

/**
 * Class CreateAssessmentReportSectionPhotoRequest
 *
 * @package App\Http\Requests\AssessmentReports
 *
 * @OA\Schema(
 *     type="object",
 *     required={"position"},
 *     @OA\Property(
 *         property="photo_id",
 *         description="Photo identifier",
 *         type="int",
 *         example=1,
 *         nullable=true,
 *     ),
 *     @OA\Property(
 *         property="position",
 *         description="Position of AR section image",
 *         type="int",
 *         example=10,
 *     ),
 *     @OA\Property(
 *         property="caption",
 *         description="AR section image caption",
 *         type="string",
 *         example="Section caption",
 *         nullable=true,
 *     ),
 * )
 */
class CreateAssessmentReportSectionPhotoRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'photo_id' => 'nullable|integer|exists:photos,id',
            'position' => 'required|integer',
            'caption'  => 'nullable|string',
        ];
    }
}
