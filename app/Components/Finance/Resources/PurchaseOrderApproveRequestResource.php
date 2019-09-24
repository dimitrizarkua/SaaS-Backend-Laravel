<?php

namespace App\Components\Finance\Resources;

use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderApproveRequestResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     required={"purchase_order_id, requester", "approver"}
 * )
 *
 * @mixin \App\Components\Finance\Models\PurchaseOrderApproveRequest
 */
class PurchaseOrderApproveRequestResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="purchase_order_id",
     *      description="Purchase order id",
     *      type="id",
     *      example="1"
     * ),
     * @OA\Property(
     *      property="requester",
     *      ref="#/components/schemas/UserProfileMiniResource",
     * ),
     * @OA\Property(
     *      property="approver",
     *      ref="#/components/schemas/UserProfileMiniResource",
     * ),
     * @OA\Property(
     *      property="approved_at",
     *      description="Date time when approve request was approved",
     *      type="string",
     *      format="date-time",
     *      nullable=true
     * ),
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
        $result['approver']  = UserProfileMiniResource::make($this->approver);
        $result['requester'] = UserProfileMiniResource::make($this->requester);
        unset($result['requester_id'], $result['approver_id']);

        return $result;
    }
}
