<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobReimbursementResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobReimbursementResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobReimbursementResponse extends ApiOKResponse
{
    protected $resource = JobReimbursementResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobReimbursementResource"
     * )
     *
     * @var JobReimbursementResource
     */
    protected $data;
}
