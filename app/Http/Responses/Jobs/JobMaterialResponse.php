<?php
namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobMaterialResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobMaterialResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobMaterialResponse extends ApiOKResponse
{
    protected $resource = JobMaterialResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobMaterialResource"
     * )
     *
     * @var JobMaterialResource
     */
    protected $data;
}
