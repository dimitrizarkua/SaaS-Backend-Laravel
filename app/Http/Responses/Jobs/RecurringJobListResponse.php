<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\RecurringJobResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class RecurringJobListResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class RecurringJobListResponse extends ApiOKResponse
{
    protected $resource = RecurringJobResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RecurringJobResource")
     * ),
     * @var \App\Components\Jobs\Models\RecurringJob[]
     */
    protected $data;
}
