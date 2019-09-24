<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use App\Rules\BelongsToLocation;
use OpenApi\Annotations as OA;

/**
 * Class ForwardedPaymentRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "source_gl_account_id",
 *          "destination_gl_account_id",
 *          "transferred_at",
 *          "remittance_reference",
 *          "invoices_ids",
 *          "location_id",
 *     },
 *     @OA\Property(
 *         property="source_gl_account_id",
 *         description="Source GL account identifier.",
 *         type="integer",
 *         example="1",
 *     ),
 *     @OA\Property(
 *         property="destination_gl_account_id",
 *         description="Destination gl account identifier.",
 *         type="integer",
 *         example="2",
 *     ),
 *     @OA\Property(
 *         property="transferred_at",
 *         description="Payment date for forwarded payments.",
 *         type="string",
 *         format="date-time",
 *         example="2018-11-10T09:10:11Z",
 *     ),
 *     @OA\Property(
 *         property="remittance_reference",
 *         description="Remittance reference text.",
 *         type="string",
 *         example="Payment for invoices 1,2,3"
 *     ),
 *     @OA\Property(
 *         property="location_id",
 *         description="Location identifier.",
 *         type="integer",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="invoices_ids",
 *         description="Invoices identifiers which should be forwarded.",
 *         type="array",
 *         @OA\Items(
 *              type="integer",
 *              example="1"
 *         )
 *     )
 * )
 */
class ForwardedPaymentRequest extends ApiRequest
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
            'source_gl_account_id'      => 'required|integer|exists:gl_accounts,id|different:destination_gl_account_id',
            'destination_gl_account_id' => 'required|integer|exists:gl_accounts,id|different:source_gl_account_id',
            'location_id'               => [
                'required',
                'integer',
                'exists:locations,id',
                new BelongsToLocation($this->user()),
            ],
            'invoices_ids'              => 'required|array',
            'invoices_ids.*'            => 'required|integer|exists:invoice_payment,invoice_id',
            'remittance_reference'      => 'required|string',
            'transferred_at'            => 'required|date_format:Y-m-d\TH:i:s\Z',
        ];
    }

    /**
     * Returns source GL Account identifier.
     *
     * @return int
     */
    public function getSourceGLAccountId(): int
    {
        return $this->input('source_gl_account_id');
    }

    /**
     * Returns destination GL Account identifier.
     *
     * @return int
     */
    public function getDestinationGLAccountId(): int
    {
        return $this->input('destination_gl_account_id');
    }

    /**
     * Returns invoices identifiers.
     *
     * @return array
     */
    public function getUnforwardedInvoicesIds(): array
    {
        return $this->input('invoices_ids');
    }
}
