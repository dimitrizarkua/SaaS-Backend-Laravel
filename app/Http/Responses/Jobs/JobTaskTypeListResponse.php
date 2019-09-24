<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;

/**
 * Class JobTaskTypeListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobTaskTypeListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobTaskType")
     * ),
     * @var \App\Components\Jobs\Models\JobTaskType[]
     */
    protected $data;
}
