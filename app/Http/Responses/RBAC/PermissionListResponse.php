<?php

namespace App\Http\Responses\RBAC;

use App\Components\RBAC\Resources\PermissionResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class PermissionListResponse
 *
 * @package App\Http\Responses\RBAC
 * @OA\Schema(required={"data"})
 */
class PermissionListResponse extends ApiOKResponse
{
    protected $resource = PermissionResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Permission")
     * )
     * @var \App\Components\RBAC\Models\Permission[]
     */
    protected $data;
}
