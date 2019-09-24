<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FlooringTypeResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class FlooringTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/FlooringType")
     * @var \App\Components\AssessmentReports\Models\FlooringType
     */
    protected $data;
}
