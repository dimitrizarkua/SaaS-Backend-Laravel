<?php
namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobServiceResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobServiceResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobService")
     * @var \App\Components\Jobs\Models\JobService
     */
    protected $data;
}
