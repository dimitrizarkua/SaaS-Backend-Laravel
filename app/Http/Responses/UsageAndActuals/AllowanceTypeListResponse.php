<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AllowanceTypeListResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class AllowanceTypeListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AllowanceType")
     * ),
     * @var \App\Components\UsageAndActuals\Models\AllowanceType[]
     */
    protected $data;
}
