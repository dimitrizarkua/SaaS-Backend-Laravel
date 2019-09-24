<?php

namespace App\Http\Responses\AssessmentReports;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetTypeListResponse
 *
 * @package App\Http\Responses\AssessmentReports
 * @OA\Schema(required={"data"})
 */
class CarpetTypeListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/CarpetType")
     * )
     * @var \App\Components\AssessmentReports\Models\CarpetType[]
     */
    protected $data;
}
