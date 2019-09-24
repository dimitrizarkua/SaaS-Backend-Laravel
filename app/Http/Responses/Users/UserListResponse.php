<?php

namespace App\Http\Responses\Users;

use App\Components\Users\Resources\UserListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class UserListResponse
 *
 * @package App\Http\Responses\Users
 *
 * @OA\Schema(required={"data"})
 */
class UserListResponse extends ApiOKResponse
{
    protected $resource = UserListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserListResource")
     * )
     *
     * @var \App\Components\Users\Resources\UserListResource[]
     */
    protected $data;
}
