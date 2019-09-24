<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class RunResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class RunResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobRun")
     * @var \App\Components\Operations\Models\JobRun
     */
    protected $data;
}
