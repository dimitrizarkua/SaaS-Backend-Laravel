<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\SearchJobResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class SearchJobResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class SearchJobResponse extends ApiOKResponse
{
    protected $resource = SearchJobResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/SearchJobResource"
     * )
     *
     * @var SearchJobResource
     */
    protected $data;
}
