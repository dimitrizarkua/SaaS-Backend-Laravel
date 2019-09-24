<?php

namespace App\Components\Finance\Resources;

use App\Components\Finance\Interfaces\PurchaseOrderInfoInterface;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderInfoResource
 *
 * @package App\Components\Finance\Resources
 * @property PurchaseOrderInfoInterface $resource
 *
 * @OA\Schema(
 *     type="object",
 *     required={"draft","pending_approval","approved"},
 * )
 */
class PurchaseOrderInfoResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="draft",
     *     description="Count and amount for purchase orders in the Draft tab",
     *     ref="#/components/schemas/CounterItem"
     * ),
     * @OA\Property(
     *     property="pending_approval",
     *     description="Count and amount for purchase orders in the Pending Approval tab",
     *     ref="#/components/schemas/CounterItem"
     * ),
     * @OA\Property(
     *     property="approved",
     *     description="Count and amount for purchase orders in the Approved tab",
     *     ref="#/components/schemas/CounterItem"
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
        return [
            'draft'            => $this->resource->getDraftCounter(),
            'pending_approval' => $this->resource->getPendingApprovalCounter(),
            'approved'         => $this->resource->getApprovedCounter(),
        ];
    }
}
