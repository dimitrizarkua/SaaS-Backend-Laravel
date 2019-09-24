<?php

namespace App\Http\Responses\Users;

use App\Components\Core\Resources\UserWithLocationsResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class UsersWithLocationsResponse
 *
 * @package App\Http\Responses\Users
 *
 * @OA\Schema(required={"data"})
 */
class UsersWithLocationsResponse extends ApiOKResponse
{
    protected $resource = UserWithLocationsResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserWithLocationsResource")
     * )
     *
     * @var \App\Models\User[]
     */
    protected $data;
}
