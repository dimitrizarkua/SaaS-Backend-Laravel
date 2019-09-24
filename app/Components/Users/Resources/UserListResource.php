<?php

namespace App\Components\Users\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserListResource
 *
 * @package App\Components\Users\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class UserListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="avatar",
     *     description="Full url to avatar image",
     *     type="string",
     *     nullable=true,
     *     example="http://url-to-photo"
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

        $result['avatar'] = null !== $this->avatar ? $this->avatar->url : null;

        return $result;
    }
}
