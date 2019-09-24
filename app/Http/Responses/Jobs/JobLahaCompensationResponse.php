<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobLahaCompensationResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobLahaCompensationResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobLahaCompensationResponse extends ApiOKResponse
{
    protected $resource = JobLahaCompensationResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobLahaCompensationResource"
     * )
     *
     * @var JobLahaCompensationResource
     */
    protected $data;
}
