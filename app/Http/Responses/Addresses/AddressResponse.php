<?php

namespace App\Http\Responses\Addresses;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AddressResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class AddressResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Address")
     * @var \App\Components\Addresses\Models\Address
     */
    protected $data;
}
