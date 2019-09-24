<?php

namespace App\Components\Finance\Resources;

use OpenApi\Annotations as OA;

/**
 * Class CreditNoteResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/CreditNote"),
 *         @OA\Schema(ref="#/components/schemas/FinancialEntityResource"),
 *     },
 * )
 * @mixin \App\Components\Finance\Models\CreditNote
 */
class CreditNoteResource extends FinancialEntityResource
{
    /**
     * @OA\Property(
     *     property="payment",
     *     type="object",
     *     nullable=true,
     *     @OA\Schema(
     *         ref="#/components/schemas/PaymentDetailsResource"
     *     )
     * ),
     * @OA\Property(
     *     property="items",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/CreditNoteItemResource")
     * ),
     * @OA\Property(
     *     property="approve_requests",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/CreditNoteApproveRequest")
     * ),
     * @OA\Property(
     *     property="transactions",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FullTransactionResource")
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
        $result = parent::toArray($request);

        $result['payment']          = PaymentDetailsResource::make($this->payment);
        $result['items']            = CreditNoteItemResource::collection($this->items);
        $result['approve_requests'] = $this->approveRequests->toArray();
        $result['transactions']     = $this->transactions->toArray();

        return $result;
    }
}
