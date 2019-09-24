<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class MeasureUnitResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class MeasureUnitResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/MeasureUnit"
     * ),
     * @var \App\Components\UsageAndActuals\Models\MeasureUnit
     */
    protected $data;
}
