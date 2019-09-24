<?php

namespace App\Http\Responses\RBAC;

use App\Components\RBAC\Resources\RoleResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class FullRoleResponse
 *
 * @package App\Http\Responses\RBAC
 * @OA\Schema(required={"data"})
 */
class FullRoleResponse extends ApiOKResponse
{
    protected $resource = RoleResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/RoleResource"
     * ),
     * @var \App\Components\RBAC\Resources\RoleResource
     */
    protected $data;
}
