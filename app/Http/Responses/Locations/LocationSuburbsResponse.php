<?php

namespace App\Http\Responses\Locations;

use App\Components\Locations\Resources\LocationSuburbResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class LocationSuburbsResponse
 *
 * @package App\Http\Responses\Locations
 *
 * @OA\Schema(required={"data"})
 */
class LocationSuburbsResponse extends ApiOKResponse
{
    protected $resource = LocationSuburbResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Suburb")
     * )
     *
     * @var \App\Components\Addresses\Models\Suburb[]
     */
    protected $data;
}
