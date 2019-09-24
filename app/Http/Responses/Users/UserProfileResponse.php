<?php

namespace App\Http\Responses\Users;

use App\Components\Users\Resources\UserProfileResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class UserProfileResponse
 *
 * @package App\Http\Responses\Users
 *
 * @OA\Schema(required={"data"})
 */
class UserProfileResponse extends ApiOKResponse
{
    protected $resource = UserProfileResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/UserProfileResource",)
     * @var \App\Components\Users\Resources\UserProfileResource
     */
    protected $data;
}
