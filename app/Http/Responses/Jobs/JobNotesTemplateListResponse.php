<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobNotesTemplateListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobNotesTemplateListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobNotesTemplateListResponse extends ApiOKResponse
{
    protected $resource = JobNotesTemplateListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobNotesTemplateListResource")
     * )
     *
     * @var \App\Components\Jobs\Resources\JobNotesTemplateListResource[]
     */
    protected $data;
}
