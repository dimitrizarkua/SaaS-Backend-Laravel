<?php

namespace App\Http\Responses\Locations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class LocationResponse
 *
 * @package App\Http\Responses\Locations
 * @OA\Schema(required={"data"})
 */
class LocationResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Location")
     * @var \App\Components\Locations\Models\Location
     */
    protected $data;
}
