<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobStatusResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobStatusHistoryListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobStatusHistoryListResponse extends ApiOKResponse
{
    protected $resource = JobStatusResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *      @OA\Items(ref="#/components/schemas/JobStatusResource")
     * )
     *
     * @var \App\Components\Jobs\Models\JobStatus[]
     */
    protected $data;
}
