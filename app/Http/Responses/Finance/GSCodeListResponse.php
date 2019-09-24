<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\GSCodeResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class GSCodeListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class GSCodeListResponse extends ApiOKResponse
{
    protected $resource = GSCodeResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/GSCodeResource")
     * ),
     * @var \App\Components\Finance\Resources\GSCodeResource[]
     */
    protected $data;
}
