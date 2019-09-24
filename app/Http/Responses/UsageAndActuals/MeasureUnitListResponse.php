<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class MeasureUnitListResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class MeasureUnitListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/MeasureUnit")
     * ),
     * @var \App\Components\UsageAndActuals\Models\MeasureUnit[]
     */
    protected $data;
}
