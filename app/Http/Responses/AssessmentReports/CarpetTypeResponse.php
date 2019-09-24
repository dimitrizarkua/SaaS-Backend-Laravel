<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetTypeResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class CarpetTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/CarpetType")
     * @var \App\Components\AssessmentReports\Models\CarpetType
     */
    protected $data;
}
