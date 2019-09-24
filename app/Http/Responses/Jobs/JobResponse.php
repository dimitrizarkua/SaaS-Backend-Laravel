<?php
namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Job")
     * @var \App\Components\Jobs\Models\Job
     */
    protected $data;
}
