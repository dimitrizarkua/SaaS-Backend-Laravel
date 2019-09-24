<?php

namespace App\Http\Responses\RBAC;

use App\Http\Responses\ApiOKResponse;

/**
 * Class RoleListResponse
 *
 * @package App\Http\Responses\RBAC
 * @OA\Schema(required={"data"})
 */
class RoleListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Role")
     * )
     *
     * @var \App\Components\RBAC\Models\Role[]
     */
    protected $data;
}
