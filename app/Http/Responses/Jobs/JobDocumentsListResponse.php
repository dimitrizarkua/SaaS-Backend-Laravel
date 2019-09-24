<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobDocumentListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobDocumentsListResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class JobDocumentsListResponse extends ApiOKResponse
{
    protected $resource = JobDocumentListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobDocumentListResource")
     * ),
     * @var \App\Components\Jobs\Resources\JobDocumentListResource[]
     */
    protected $data;
}
