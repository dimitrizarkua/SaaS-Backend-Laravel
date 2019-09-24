<?php

namespace App\Http\Responses\Addresses;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class StateListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class StateListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/State")
     * )
     * @var \App\Components\Addresses\Models\State[]
     */
    protected $data;
}
