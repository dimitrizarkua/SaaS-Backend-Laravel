<?php

namespace App\Http\Responses\Contacts;

use App\Components\Contacts\Resources\FullContactResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullContactResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Contacts
 */
class FullContactResponse extends ApiOKResponse
{
    protected $resource = FullContactResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullContactResource")
     * @var \App\Components\Contacts\Resources\FullContactResource
     */
    protected $data;
}
