<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\FullRunTemplateRunResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullRunTemplateRunResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class FullRunTemplateRunResponse extends ApiOKResponse
{
    protected $resource = FullRunTemplateRunResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullRunTemplateRunResource")
     * @var \App\Components\Operations\Resources\FullRunTemplateRunResource
     */
    protected $data;
}
