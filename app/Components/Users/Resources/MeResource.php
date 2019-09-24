<?php

namespace App\Components\Users\Resources;

use App\Components\RBAC\Models\Permission;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class MeResource
 *
 * @package App\Components\Users\Resources
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/UserProfileResource")},
 * )
 */
class MeResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="permissions",
     *     description="List of all permissions assigned to the user",
     *     type="array",
     *     @OA\Items(
     *          type="string",
     *          description="Permisssion name",
     *          example="addresses.create"
     *     )
     * )
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
        $result                = UserProfileResource::make($this->resource['user'])
            ->toArray($request);
        $result['permissions'] = $this->resource['permissions']->map(function (Permission $permission) {
            return $permission->getName();
        });

        return $result;
    }
}
