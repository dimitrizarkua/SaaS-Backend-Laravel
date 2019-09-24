<?php

namespace App\Http\Responses\RBAC;

use App\Http\Responses\ApiOKResponse;

/**
 * Class RoleResponse
 *
 * @package App\Http\Responses\RBAC
 * @OA\Schema(required={"data"})
 */
class RoleResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Role")
     * @var \App\Components\RBAC\Models\Role
     */
    protected $data;
}
