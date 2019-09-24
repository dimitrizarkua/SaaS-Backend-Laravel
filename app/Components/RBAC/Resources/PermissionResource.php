<?php

namespace App\Components\RBAC\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class PermissionResource
 *
 * @package App\Components\RBAC\Resources
 * @mixin \App\Components\RBAC\Models\Permission
 */
class PermissionResource extends JsonResource
{
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
            'name'         => $this->getName(),
            'description'  => $this->getDescription(),
            'display_name' => $this->getDisplayName(),
        ];
    }
}
