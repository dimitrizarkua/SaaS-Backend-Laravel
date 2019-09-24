<?php

namespace App\Http\Requests\AssessmentReports;

use App\Components\AssessmentReports\Enums\AssessmentReportStatuses;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * Class ChangeAssessmentReportStatusRequest
 *
 * @package App\Http\Requests\AssessmentReports
 * @OA\Schema(
 *     type="object",
 *     required={"status"},
 *     @OA\Property(
 *         property="status",
 *         description="New status of the assessment report",
 *         ref="#/components/schemas/AssessmentReportStatuses",
 *     ),
 * )
 */
class ChangeAssessmentReportStatusRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(AssessmentReportStatuses::values())],
        ];
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->get('status');
    }
}
