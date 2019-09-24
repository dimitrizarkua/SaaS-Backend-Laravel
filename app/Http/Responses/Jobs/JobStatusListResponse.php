<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobStatusListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobStatusListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(
     *          type="string",
     *          description="Job status",
     *          enum={"New","On-Hold","In-Progress","Closed","Cancelled"}
     *     )
     * )
     *
     * @var string[]
     */
    protected $data;
}
