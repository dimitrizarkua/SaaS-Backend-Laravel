<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AllowanceTypeResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class AllowanceTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/AllowanceType"
     * ),
     * @var \App\Components\UsageAndActuals\Models\AllowanceType
     */
    protected $data;
}
