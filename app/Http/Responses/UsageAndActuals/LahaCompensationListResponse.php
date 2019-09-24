<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class LahaCompensationListResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class LahaCompensationListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/LahaCompensation")
     * ),
     * @var \App\Components\UsageAndActuals\Models\LahaCompensation[]
     */
    protected $data;
}
