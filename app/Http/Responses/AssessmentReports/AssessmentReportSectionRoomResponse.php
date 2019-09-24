<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportSectionRoomResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportSectionRoomResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/AssessmentReportSectionRoom")
     * @var \App\Components\AssessmentReports\Models\AssessmentReportSectionRoom
     */
    protected $data;
}
