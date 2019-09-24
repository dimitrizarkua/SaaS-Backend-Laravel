<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Components\UsageAndActuals\Resources\MaterialResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class MaterialListResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class MaterialListResponse extends ApiOKResponse
{
    protected $resource = MaterialResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/MaterialResource")
     * ),
     * @var \App\Components\UsageAndActuals\Resources\MaterialResource[]
     */
    protected $data;
}
