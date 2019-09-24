<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\GSCodeResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class GSCodeResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class GSCodeResponse extends ApiOKResponse
{
    protected $resource = GSCodeResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/GSCodeResource"
     * ),
     * @var \App\Components\Finance\Resources\GSCodeResource
     */
    protected $data;
}
