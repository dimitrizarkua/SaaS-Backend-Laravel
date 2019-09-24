<?php

namespace App\Http\Requests\Finance;

use App\Components\Finance\Models\VO\PaymentInvoiceItem;
use App\Http\Requests\ApiRequest;

/**
 * Class CreatePaymentByCreditNoteRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"payment_items, credit_note"},
 *     @OA\Property(
 *          property="payment_items",
 *          type="array",
 *          @OA\Items(
 *              type="object",
 *              required={"invoice_id","amount"},
 *              @OA\Property(
 *                  property="invoice_id",
 *                  description="Invoice identifier.",
 *                  type="int",
 *                  example="1",
 *              ),
 *              @OA\Property(
 *                  property="amount",
 *                  description="Amount of specific payment item.",
 *                  type="float",
 *                  example="10.4",
 *              ),
 *          ),
 *     ),
 * )
 */
class CreatePaymentByCreditNoteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @see https://laravel.com/docs/5.7/validation#available-validation-rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payment_items'              => 'required|array',
            'payment_items.*.invoice_id' => 'required|exists:invoices,id',
            'payment_items.*.amount'     => 'required|numeric',
        ];
    }

    /**
     * Returns list of payment items.
     *
     * @return array
     */
    public function getPaymentItems(): array
    {
        return array_map(function ($item) {
            return new PaymentInvoiceItem($item);
        }, $this->input('payment_items'));
    }
}
