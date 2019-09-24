<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Components\UsageAndActuals\Resources\InsurerContractResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullInsurerContractResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class FullInsurerContractResponse extends ApiOKResponse
{
    protected $resource = InsurerContractResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/InsurerContractResource"
     * ),
     * @var \App\Components\UsageAndActuals\Resources\InsurerContractResource
     */
    protected $data;
}
