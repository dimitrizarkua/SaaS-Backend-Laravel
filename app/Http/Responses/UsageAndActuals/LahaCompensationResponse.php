<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class LahaCompensationResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class LahaCompensationResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/LahaCompensation"
     * ),
     * @var \App\Components\UsageAndActuals\Models\LahaCompensation
     */
    protected $data;
}
