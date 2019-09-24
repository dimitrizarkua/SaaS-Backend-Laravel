<?php

namespace App\Components\Jobs\Resources;

use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobAllowanceResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\JobAllowance
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/JobAllowance")},
 * )
 */
class JobAllowanceResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="allowance_type",
     *     ref="#/components/schemas/AllowanceType",
     * ),
     * @OA\Property(
     *      property="user",
     *      ref="#/components/schemas/UserProfileMiniResource",
     * ),
     * @OA\Property(
     *     property="total_amount",
     *     description="Total amount of job allowance",
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
        $result                   = $this->resource->toArray();
        $result['allowance_type'] = $this->allowanceType;
        $result['user']           = UserProfileMiniResource::make($this->user);
        $result['total_amount']   = $this->charge_rate_per_interval * $this->amount;

        return $result;
    }
}
