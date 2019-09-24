<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Components\UsageAndActuals\Resources\MaterialResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class MaterialResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class MaterialResponse extends ApiOKResponse
{
    protected $resource = MaterialResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/MaterialResource"
     * ),
     * @var \App\Components\UsageAndActuals\Resources\MaterialResource
     */
    protected $data;
}
