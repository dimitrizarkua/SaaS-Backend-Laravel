<?php

namespace App\Http\Responses\Addresses;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CountryResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class CountryResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Country")
     * @var \App\Components\Addresses\Models\Country
     */
    protected $data;
}
