<?php

namespace App\Components\Core\Resources;

use App\Components\Locations\Resources\UserLocationResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserWithLocationsResource
 *
 * @package App\Components\Core\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class UserWithLocationsResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="locations",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserLocationResource")
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
        $result = $this->resource->toArray();

        $result['locations'] = UserLocationResource::collection($this->resource->locations);

        return $result;
    }
}
