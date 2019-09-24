<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\FullJobNoteResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobNotesListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobNotesListResponse extends ApiOKResponse
{
    protected $resource = FullJobNoteResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FullJobNoteResource")
     * )
     *
     * @var \App\Components\Jobs\Resources\FullJobNoteResource[]
     */
    protected $data;
}
