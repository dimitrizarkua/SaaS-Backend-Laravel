<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class RunTemplateRunResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class RunTemplateRunResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobRunTemplateRun")
     * @var \App\Components\Operations\Models\JobRunTemplateRun
     */
    protected $data;
}
