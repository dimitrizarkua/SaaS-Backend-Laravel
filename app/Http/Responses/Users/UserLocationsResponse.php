<?php

namespace App\Http\Responses\Users;

use App\Components\Locations\Resources\UserLocationResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class UserLocationsResponse
 *
 * @package App\Http\Responses\Users
 *
 * @OA\Schema(required={"data"})
 */
class UserLocationsResponse extends ApiOKResponse
{
    protected $resource = UserLocationResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserLocationResource")
     * )
     *
     * @var \App\Components\Locations\Models\Location[]
     */
    protected $data;
}
