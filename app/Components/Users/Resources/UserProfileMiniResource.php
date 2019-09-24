<?php

namespace App\Components\Users\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserProfileMiniResource
 *
 * @package App\Components\Users\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","email","full_name"}
 * )
 */
class UserProfileMiniResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="email",
     *     type="string",
     *     example="test@steamatic.com.au"
     * ),
     * @OA\Property(
     *     property="full_name",
     *     type="string",
     *     example="John Smith",
     *     nullable=true
     * ),
     * @OA\Property(
     *     property="avatar",
     *     description="Full url to avatar image",
     *     type="string",
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
        $result = [
            'id'        => $this->id,
            'email'     => $this->email,
            'full_name' => $this->full_name,
        ];

        if (isset($this->avatar)) {
            $result['avatar'] = $this->avatar->url;
        }

        return $result;
    }
}
