<?php

namespace App\Http\Responses\RBAC;

use App\Components\RBAC\Resources\RoleResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class FullRoleListResponse
 *
 * @package App\Http\Responses\RBAC
 * @OA\Schema(required={"data"})
 */
class FullRoleListResponse extends ApiOKResponse
{
    protected $resource = RoleResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/RoleResource")
     * )
     * @var \App\Components\RBAC\Models\Role[]
     */
    protected $data;
}
