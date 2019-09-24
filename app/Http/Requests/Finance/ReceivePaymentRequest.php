<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class ReceivePaymentRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"payment_data","invoices_list"},
 *     @OA\Property(
 *         property="payment_data",
 *         description="Invoice payments data",
 *         type="object",
 *         required={"paid_at","location_id","dst_gl_account_id"},
 *         @OA\Property(
 *             property="paid_at",
 *             type="string",
 *             format="date-time",
 *             example="2018-11-10T09:10:11Z"
 *         ),
 *         @OA\Property(
 *             property="reference",
 *             type="string",
 *             description="Reference",
 *             example="Reference",
 *         ),
 *         @OA\Property(
 *             property="location_id",
 *             type="integer",
 *             description="Location identifier.",
 *             example="1",
 *         ),
 *         @OA\Property(
 *             property="dst_gl_account_id",
 *             type="integer",
 *             description="Paid into Account identifier",
 *             example="1",
 *         ),
 *     ),
 *     @OA\Property(
 *         property="invoices_list",
 *         description="List of invoices",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             required={"invoice_id","is_forwarded","amount"},
 *             @OA\Property(
 *                 property="invoice_id",
 *                 type="integer",
 *                 description="Invoice identifier",
 *                 example=1,
 *             ),
 *             @OA\Property(
 *                 property="is_fp",
 *                 type="boolean",
 *                 description="Indicates whether payment should be forwarded.",
 *                 example=1,
 *             ),
 *             @OA\Property(
 *                 property="amount",
 *                 type="float",
 *                 description="Invoice payment amount",
 *                 example="99.99",
 *             ),
 *         ),
 *     ),
 * )
 */
class ReceivePaymentRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'payment_data'                   => 'required|array',
            'payment_data.paid_at'           => 'required|date_format:Y-m-d\TH:i:s\Z',
            'payment_data.reference'         => 'nullable|string',
            'payment_data.location_id'       => 'required|integer|exists:locations,id',
            'payment_data.dst_gl_account_id' => 'required|integer|exists:gl_accounts,id',
            'invoices_list'                  => 'required|array',
            'invoices_list.*.invoice_id'     => 'required|int|exists:invoices,id',
            'invoices_list.*.is_fp'          => 'required|boolean',
            'invoices_list.*.amount'         => 'required|numeric',
        ];
    }

    /**
     * @return array
     */
    public function getInvoicesList(): array
    {
        return $this->get('invoices_list');
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        $paymentData = $this->validated()['payment_data'];

        return [
            'paidAt'    => $paymentData['paid_at'],
            'reference' => $paymentData['reference'],
        ];
    }

    /**
     * @return array
     */
    public function getDstGLAccountId()
    {
        return $this->validated()['payment_data']['dst_gl_account_id'];
    }

    /**
     * @return array
     */
    public function getLocationId()
    {
        return $this->validated()['payment_data']['location_id'];
    }
}
