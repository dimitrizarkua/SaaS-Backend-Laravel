<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\FullRunResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullRunResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class FullRunResponse extends ApiOKResponse
{
    protected $resource = FullRunResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullRunResource")
     * @var \App\Components\Operations\Resources\FullRunResource
     */
    protected $data;
}
