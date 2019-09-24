<?php

namespace App\Http\Responses\Addresses;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class SuburbResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class SuburbResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Suburb")
     * @var \App\Components\Addresses\Models\Suburb
     */
    protected $data;
}
