<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetFaceFibreResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class CarpetFaceFibreResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/CarpetFaceFibre")
     * @var \App\Components\AssessmentReports\Models\CarpetFaceFibre
     */
    protected $data;
}
