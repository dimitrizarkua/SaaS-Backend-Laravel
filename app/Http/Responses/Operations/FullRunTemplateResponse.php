<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\FullRunTemplateResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullRunTemplateResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class FullRunTemplateResponse extends ApiOKResponse
{
    protected $resource = FullRunTemplateResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullRunTemplateResource")
     * @var \App\Components\Operations\Resources\FullRunTemplateResource
     */
    protected $data;
}
