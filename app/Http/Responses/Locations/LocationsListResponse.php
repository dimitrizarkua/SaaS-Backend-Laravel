<?php

namespace App\Http\Responses\Locations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class LocationsListResponse
 *
 * @package App\Http\Responses\Locations
 * @OA\Schema(required={"data"})
 */
class LocationsListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Location")
     * )
     *
     * @var \App\Components\Locations\Models\Location[]
     */
    protected $data;
}
