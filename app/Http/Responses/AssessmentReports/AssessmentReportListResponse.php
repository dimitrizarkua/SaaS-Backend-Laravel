<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssessmentReportListResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class AssessmentReportListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AssessmentReport")
     * )
     * @var \App\Components\AssessmentReports\Models\AssessmentReport[]
     */
    protected $data;
}
