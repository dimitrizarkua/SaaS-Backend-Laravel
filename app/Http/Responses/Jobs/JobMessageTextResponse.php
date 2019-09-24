<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobMessageTextResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobMessageTextResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="string",
     * )
     *
     * @var string
     */
    protected $data;
}
