<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class UnderlayTypeResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class UnderlayTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/UnderlayType")
     * @var \App\Components\AssessmentReports\Models\UnderlayType
     */
    protected $data;
}
