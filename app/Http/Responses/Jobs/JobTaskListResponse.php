<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobTaskListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class JobTaskListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobTaskListResponse extends ApiOKResponse
{
    protected $resource = JobTaskListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobTaskListResource")
     * ),
     * @var \App\Components\Jobs\Resources\JobTaskListResource[]
     */
    protected $data;
}
