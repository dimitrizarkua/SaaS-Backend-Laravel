<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     allOf={
 *          @OA\Schema(ref="#/components/schemas/PurchaseOrder"),
 *          @OA\Schema(ref="#/components/schemas/FinancialEntityResource"),
 *     },
 * )
 * @mixin \App\Components\Finance\Models\PurchaseOrder
 */
class PurchaseOrderResource extends FinancialEntityResource
{
    /**
     * @OA\Property(
     *     property="items",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/PurchaseOrderItemResource")
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
        $result          = parent::toArray($request);
        $result['items'] = PurchaseOrderItemResource::collection($this->items);

        return $result;
    }
}
