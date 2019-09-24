<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobAllowanceResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobAllowanceResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobAllowanceResponse extends ApiOKResponse
{
    protected $resource = JobAllowanceResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobAllowanceResource"
     * )
     *
     * @var JobAllowanceResource
     */
    protected $data;
}
