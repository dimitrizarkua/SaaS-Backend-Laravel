<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class GSCodeResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\GSCode
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/GSCode")},
 * )
 */
class GSCodeResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="invoice_items",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/InvoiceItemResource")
     * ),
     * @OA\Property(
     *     property="purchase_order_items",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/PurchaseOrderItemResource")
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

        $result['invoice_items'] = null;
        if (isset($this->invoiceItems)) {
            $result['invoice_items'] = $this->invoiceItems->toArray();
        }

        $result['purchase_order_items'] = null;
        if (isset($this->purchaseOrderItems)) {
            $result['purchase_order_items'] = $this->purchaseOrderItems->toArray();
        }

        //todo add credit notes

        return $result;
    }
}
