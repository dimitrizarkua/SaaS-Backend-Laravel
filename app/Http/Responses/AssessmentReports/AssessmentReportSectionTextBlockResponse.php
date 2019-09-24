<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionTextBlockResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportSectionTextBlockResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/AssessmentReportSectionTextBlock")
     * @var \App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock
     */
    protected $data;
}
