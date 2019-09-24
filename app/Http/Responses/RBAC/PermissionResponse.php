<?php

namespace App\Http\Responses\RBAC;

use App\Components\RBAC\Resources\PermissionResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class PermissionResponse
 *
 * @package App\Http\Responses\RBAC
 * @OA\Schema(required={"data"})
 */
class PermissionResponse extends ApiOKResponse
{
    protected $resource = PermissionResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/Permission")
     * @var \App\Components\RBAC\Models\Permission
     */
    protected $data;
}
