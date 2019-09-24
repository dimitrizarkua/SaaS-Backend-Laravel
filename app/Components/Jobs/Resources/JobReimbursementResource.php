<?php

namespace App\Components\Jobs\Resources;

use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobReimbursementResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobReimbursement
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobReimbursement")},
 * )
 */
class JobReimbursementResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="user",
     *      ref="#/components/schemas/UserProfileMiniResource",
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result         = $this->resource->toArray();
        $result['user'] = UserProfileMiniResource::make($this->user);

        return $result;
    }
}
