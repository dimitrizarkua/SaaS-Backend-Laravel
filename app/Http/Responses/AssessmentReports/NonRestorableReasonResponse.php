<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class NonRestorableReasonResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class NonRestorableReasonResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/FlooringType")
     * @var \App\Components\AssessmentReports\Models\NonRestorableReason
     */
    protected $data;
}
