<?php

namespace App\Http\Responses\Users;

use App\Components\Users\Resources\MeResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class MeResponse
 *
 * @package App\Http\Responses\Users
 *
 * @OA\Schema(required={"data"})
 */
class MeResponse extends ApiOKResponse
{
    protected $resource = MeResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/MeResource",)
     * @var \App\Components\Users\Resources\MeResource
     */
    protected $data;
}
