<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class PaymentDetailsResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\Payment
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/Payment")},
 * )
 */
class PaymentDetailsResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="transaction",
     *     ref="#/components/schemas/FullTransactionResource"
     * ),
     * @OA\Property(
     *     property="credit_card_transaction",
     *     nullable=true,
     *     ref="#/components/schemas/CreditCardTransaction"
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

        if ($this->transaction_id) {
            $result['transaction'] = FullTransactionResource::make($this->transaction);
        }

        $result['credit_card_transaction'] = null;
        if ($this->creditCardTransaction) {
            $result['credit_card_transaction'] = $this->creditCardTransaction;
        }

        return $result;
    }
}
