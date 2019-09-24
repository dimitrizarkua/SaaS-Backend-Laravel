<?php

namespace App\Components\RBAC\Resources;

use App\Components\RBAC\PermissionAwareTrait;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RoleResource
 *
 * @package App\Components\RBAC\Resources
 * @mixin \App\Components\RBAC\Models\Role
 * @OA\Schema(
 *      required={"id","name","display_name","description","permissions"}
 * )
 */
class RoleResource extends JsonResource
{

    /**
     * @OA\Property(
     *      property="id",
     *      type="integer",
     *      description="Role identifier",
     *      example=1
     * ),
     * @OA\Property(
     *      property="name",
     *      type="string",
     *      description="Role name",
     *      example="admin"
     * ),
     * @OA\Property(
     *      property="display_name",
     *      type="string",
     *      description="Display name",
     *      example="Admin"
     * ),
     * @OA\Property(
     *      property="description",
     *      type="string",
     *      description="Role description",
     *      example="Allows to manage many internal resources"
     * ),
     * @OA\Property(
     *      property="permissions",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/Permission")),
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'display_name' => $this->display_name,
            'description'  => $this->description,
            'permissions'  => PermissionResource::collection($this->getPermissions()),
        ];
    }
}
