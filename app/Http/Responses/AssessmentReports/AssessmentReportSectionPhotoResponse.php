<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionPhotoResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportSectionPhotoResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/AssessmentReportSectionPhoto")
     * @var \App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto
     */
    protected $data;
}
