<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobsInfoResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobsInfoResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class JobsInfoResponse extends ApiOKResponse
{
    protected $resource = JobsInfoResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobsInfoResource"
     * )
     *
     * @var JobsInfoResource
     */
    protected $data;
}
