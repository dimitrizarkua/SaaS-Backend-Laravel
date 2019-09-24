<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobNotesAndMessagesResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobNotesAndMessagesListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobNotesAndMessagesListResponse extends ApiOKResponse
{
    protected $resource = JobNotesAndMessagesResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     ref="#/components/schemas/JobNotesAndMessagesResource"
     * )
     *
     * @var array
     */
    protected $data;
}
