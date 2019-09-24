<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportCostItemResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportCostItemResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/AssessmentReportCostItem")
     * @var \App\Components\AssessmentReports\Models\AssessmentReportCostItem
     */
    protected $data;
}
