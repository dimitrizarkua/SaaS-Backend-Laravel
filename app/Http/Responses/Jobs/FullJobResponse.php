<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\FullJobResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullJobResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class FullJobResponse extends ApiOKResponse
{
    protected $resource = FullJobResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/FullJobResource"
     * )
     *
     * @var FullJobResource
     */
    protected $data;
}
