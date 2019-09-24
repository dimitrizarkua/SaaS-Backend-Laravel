<?php

namespace App\Components\Finance\Resources;

use App\Components\Finance\Models\PurchaseOrder;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderListResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/FinanceEntityListResource")
 *     }
 * )
 *
 * @property PurchaseOrder $resource
 */
class PurchaseOrderListResource extends FinanceEntityListResource
{
    /**
     * @OA\Property(
     *     property="reference",
     *     description="Reference",
     *     type="string",
     *     example="Some reference",
     *     nullable=true
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
        $data              = parent::toArray($request);
        $data['reference'] = $this->resource->reference;

        return $data;
    }
}
