<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetAgeResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class CarpetAgeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/CarpetAge")
     * @var \App\Components\AssessmentReports\Models\CarpetAge
     */
    protected $data;
}
