<?php

namespace App\Http\Responses\Addresses;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class StateResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class StateResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/State")
     * @var \App\Components\Addresses\Models\State
     */
    protected $data;
}
