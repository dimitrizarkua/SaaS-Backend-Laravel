<?php

namespace App\Components\Jobs\Resources;

use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobLahaCompensationResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobLahaCompensation
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobLahaCompensation")},
 * )
 */
class JobLahaCompensationResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="laha_compensation",
     *     ref="#/components/schemas/LahaCompensation",
     * ),
     * @OA\Property(
     *      property="user",
     *      ref="#/components/schemas/UserProfileMiniResource",
     * ),
     * @OA\Property(
     *     property="total_amount",
     *     description="Total amount of job laha compenstion",
     *     type="number",
     *     format="float",
     *     example=12.3
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
        $result                      = $this->resource->toArray();
        $result['laha_compensation'] = $this->lahaCompensation;
        $result['user']              = UserProfileMiniResource::make($this->user);
        $result['total_amount']      = $this->rate_per_day * $this->days;

        return $result;
    }
}
