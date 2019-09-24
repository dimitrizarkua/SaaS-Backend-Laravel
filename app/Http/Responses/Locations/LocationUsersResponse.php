<?php

namespace App\Http\Responses\Locations;

use App\Components\Locations\Resources\LocationUserResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class LocationUsersResponse
 *
 * @package App\Http\Responses\Locations
 *
 * @OA\Schema(required={"data"})
 */
class LocationUsersResponse extends ApiOKResponse
{
    protected $resource = LocationUserResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/LocationUserResource")
     * )
     *
     * @var \App\Models\User[]
     */
    protected $data;
}
