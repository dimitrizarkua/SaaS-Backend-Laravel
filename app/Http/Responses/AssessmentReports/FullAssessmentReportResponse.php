<?php

namespace App\Http\Responses\AssessmentReports;

use App\Components\AssessmentReports\Resources\FullAssessmentReportResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullAssessmentReportResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class FullAssessmentReportResponse extends ApiOKResponse
{
    protected $resource = FullAssessmentReportResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullAssessmentReportResource")
     * @var \App\Components\AssessmentReports\Resources\FullAssessmentReportResource
     */
    protected $data;
}
