<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class DirectDepositPaymentRequest
 *
 * @package App\Http\Requests\Finance
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "amount",
 *          "paid_at",
 *          "gl_account_id",
 *     },
 *     @OA\Property(
 *        property="amount",
 *        description="Payment amount",
 *        type="number",
 *        example=500.00
 *     ),
 *     @OA\Property(
 *        property="paid_at",
 *        description="Date of payment",
 *        type="string",
 *        format="date",
 *        example="2018-02-25"
 *     ),
 *     @OA\Property(
 *        property="gl_account_id",
 *        description="Identifier of GL Account to which funds will be credited",
 *        type="intger",
 *        example=1
 *     ),
 *     @OA\Property(
 *        property="reference",
 *        description="Payment reference",
 *        type="string",
 *        example="Some reference",
 *        nullable=true,
 *     )
 * )
 *
 * @property-read float  $amount
 * @property-read string $paid_at
 * @property-read int    $gl_account_id
 * @property-read string $reference
 */
class DirectDepositPaymentRequest extends ApiRequest
{
    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            'amount'        => 'required|numeric',
            'paid_at'       => 'required|date_format:Y-m-d',
            'gl_account_id' => 'required|integer|exists:gl_accounts,id',
            'reference'     => 'string|nullable',
        ];
    }
}
