<?php

namespace App\Http\Responses\Addresses;

use App\Components\Addresses\Resources\FullAddressResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullAddressResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class FullAddressResponse extends ApiOKResponse
{
    protected $resource = FullAddressResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullAddressResource")
     * @var \App\Components\Addresses\Resources\FullAddressResource
     */
    protected $data;
}
