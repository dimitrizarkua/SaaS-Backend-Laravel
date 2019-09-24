<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobRoomResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobRoomResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobRoom")
     * @var \App\Components\Jobs\Models\JobRoom
     */
    protected $data;
}
