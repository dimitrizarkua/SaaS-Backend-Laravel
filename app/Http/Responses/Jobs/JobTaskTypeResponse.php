<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskTypeResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobTaskTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobTaskType")
     * @var \App\Components\Jobs\Models\JobTaskType
     */
    protected $data;
}
