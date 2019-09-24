<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\LinkedJobsListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class LinkedJobsListResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class LinkedJobsListResponse extends ApiOKResponse
{
    protected $resource = LinkedJobsListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/LinkedJobsListResource")
     * ),
     * @var \App\Components\Jobs\Models\LinkedJob[]
     */
    protected $data;
}
