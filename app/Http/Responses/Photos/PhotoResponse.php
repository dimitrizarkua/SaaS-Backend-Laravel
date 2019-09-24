<?php

namespace App\Http\Responses\Photos;

use App\Components\Photos\Resources\PhotoResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class PhotoResponse
 *
 * @package App\Http\Responses\Photos
 * @OA\Schema(required={"data"})
 */
class PhotoResponse extends ApiOKResponse
{
    protected $resource = PhotoResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/PhotoResource")
     *
     * @var \App\Components\Photos\Resources\PhotoResource
     */
    protected $data;
}
