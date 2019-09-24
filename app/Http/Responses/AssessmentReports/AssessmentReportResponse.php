<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/AssessmentReport")
     * @var \App\Components\AssessmentReports\Models\AssessmentReport
     */
    protected $data;
}
