<?php

namespace App\Components\Users\Resources;

use App\Components\Contacts\Resources\ContactResource;
use App\Components\Locations\Resources\UserLocationResource;
use App\Components\Photos\Resources\PhotoResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserProfileResource
 *
 * @package App\Components\Users\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class UserProfileResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="avatar",
     *     ref="#/components/schemas/PhotoResource",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="locations",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserLocationResource")
     * ),
     * @OA\Property(
     *     property="contact",
     *     ref="#/components/schemas/ContactResource",
     *     nullable=true,
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $result = $this->resource->toArray();

        $result['avatar']    = PhotoResource::make($this->avatar);
        $result['locations'] = UserLocationResource::collection($this->locations);
        $result['contact']   = ContactResource::make($this->contact);

        return $result;
    }
}
