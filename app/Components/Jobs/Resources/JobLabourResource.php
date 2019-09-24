<?php

namespace App\Components\Jobs\Resources;

use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobLabourResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobLabour
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobLabour")},
 * )
 */
class JobLabourResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="labour_type",
     *     ref="#/components/schemas/LabourType",
     * ),
     * @OA\Property(
     *      property="worker",
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
        $result                = $this->resource->toArray();
        $result['labour_type'] = $this->labourType;
        $result['worker']      = UserProfileMiniResource::make($this->worker);

        return $result;
    }
}
