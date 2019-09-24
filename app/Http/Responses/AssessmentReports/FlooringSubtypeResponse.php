<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FlooringSubtypeResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class FlooringSubtypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/FlooringSubtype")
     * @var \App\Components\AssessmentReports\Models\FlooringSubtype
     */
    protected $data;
}
