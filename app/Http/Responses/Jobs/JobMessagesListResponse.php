<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\FullJobMessageResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobMessagesListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobMessagesListResponse extends ApiOKResponse
{
    protected $resource = FullJobMessageResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FullJobMessageResource")
     * )
     *
     * @var \App\Components\Messages\Models\Message[]
     */
    protected $data;
}
