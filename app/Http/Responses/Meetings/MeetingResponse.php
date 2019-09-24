<?php

namespace App\Http\Responses\Meetings;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class MeetingResponse
 *
 * @package App\Http\Responses\Meetings
 * @OA\Schema(required={"data"})
 */
class MeetingResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Meeting")
     *
     * @var \App\Components\Meetings\Models\Meeting
     */
    protected $data;
}
