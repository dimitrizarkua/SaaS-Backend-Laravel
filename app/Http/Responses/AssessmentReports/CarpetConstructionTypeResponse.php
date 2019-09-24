<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetConstructionTypeResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class CarpetConstructionTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/CarpetConstructionType")
     * @var \App\Components\AssessmentReports\Models\CarpetConstructionType
     */
    protected $data;
}
