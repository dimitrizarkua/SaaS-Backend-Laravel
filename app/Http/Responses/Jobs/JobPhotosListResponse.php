<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobPhotosListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobPhotosListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobPhotosListResponse extends ApiOKResponse
{
    protected $resource = JobPhotosListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobPhotosListResource")
     * )
     *
     * @var \App\Components\Jobs\Resources\JobPhotosListResource[]
     */
    protected $data;
}
