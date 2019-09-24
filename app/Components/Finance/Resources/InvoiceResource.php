<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class InvoiceResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\Invoice
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={
 *          @OA\Schema(ref="#/components/schemas/Invoice"),
 *          @OA\Schema(ref="#/components/schemas/FinancialEntityResource"),
 *     },
 *     required={"items","virtual_status","total_paid","amount_due"}
 * )
 */
class InvoiceResource extends FinancialEntityResource
{
    /**
     * @OA\Property(
     *     property="items",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/InvoiceItemResource")
     * ),
     * @OA\Property(
     *    property="virtual_status",
     *    ref="#/components/schemas/InvoiceVirtualStatuses"
     * ),
     * @OA\Property(
     *     property="total_paid",
     *     type="number",
     *     description="Already paid amount",
     *     example=100.00,
     * ),
     * @OA\Property(
     *     property="amount_due",
     *     type="number",
     *     description="Amount due (sub_total + taxes - total_paid)",
     *     example=4630.17,
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
        $result               = parent::toArray($request);
        $result['items']      = InvoiceItemResource::collection($this->items);
        $result['total_paid'] = $this->getTotalPaid();
        $result['amount_due'] = $this->getAmountDue();

        return $result;
    }
}
