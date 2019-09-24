<?php
namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobLabourResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobLabourResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobLabourResponse extends ApiOKResponse
{
    protected $resource = JobLabourResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobLabourResource"
     * )
     *
     * @var JobLabourResource
     */
    protected $data;
}
