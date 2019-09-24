<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\FullJobTaskResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullJobTaskResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class FullJobTaskResponse extends ApiOKResponse
{
    protected $resource = FullJobTaskResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullJobTaskResource")
     * @var \App\Components\Jobs\Resources\FullJobTaskResource
     */
    protected $data;
}
