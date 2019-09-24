<?php

namespace App\Http\Responses\Users;

use App\Components\Users\Resources\UserSearchResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class UserSearchResponse
 *
 * @package App\Http\Responses\Users
 *
 * @OA\Schema(required={"data"})
 */
class UserSearchResponse extends ApiOKResponse
{
    protected $resource = UserSearchResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserSearchResource")
     * )
     *
     * @var \App\Components\Users\Resources\UserSearchResource[]
     */
    protected $data;
}
