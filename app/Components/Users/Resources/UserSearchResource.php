<?php

namespace App\Components\Users\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserSearchResource
 *
 * @package App\Components\Operations\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class UserSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource;

        if (isset($result['location_ids'])) {
            unset($result['location_ids']);
        }

        return $result;
    }
}
