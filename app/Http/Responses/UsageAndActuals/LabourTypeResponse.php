<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class LabourTypeResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class LabourTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/LabourType"
     * ),
     * @var \App\Components\UsageAndActuals\Models\LabourType
     */
    protected $data;
}
