<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobTaskResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobTaskResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobTask")
     * @var \App\Components\Jobs\Models\JobTask
     */
    protected $data;
}
