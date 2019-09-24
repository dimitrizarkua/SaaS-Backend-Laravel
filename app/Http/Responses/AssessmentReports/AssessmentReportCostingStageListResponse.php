<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportCostingStageListResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportCostingStageListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AssessmentReportCostingStage")
     * )
     * @var \App\Components\AssessmentReports\Models\AssessmentReportCostingStage[]
     */
    protected $data;
}
