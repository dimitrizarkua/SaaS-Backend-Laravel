<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobListResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class JobListResponse extends ApiOKResponse
{
    protected $resource = JobListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobListResource")
     * )
     *
     * @var \App\Components\Jobs\Resources\JobListResource[]
     */
    protected $data;
}
