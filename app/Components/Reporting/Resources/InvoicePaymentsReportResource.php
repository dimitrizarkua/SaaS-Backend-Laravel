<?php

namespace App\Components\Reporting\Resources;

use App\Components\Reporting\Enums\PaidStatuses;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class InvoicePaymentsReportResource
 *
 * @property float $total_amount
 *
 * @package App\Components\Reporting\Resources
 * @mixin \App\Components\Finance\Models\Invoice
 * @OA\Schema(
 *     type="object",
 * )
 */
class InvoicePaymentsReportResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="id",
     *      type="integer",
     *      description="Invoice identifier",
     *      example="1"
     * ),
     * @OA\Property(
     *      property="recipient_name",
     *      description="Recipient name",
     *      type="string",
     *      example="Joshua Brown",
     * ),
     * @OA\Property(
     *      property="location_code",
     *      description="Location code.",
     *      type="string",
     *      example="SYD",
     * ),
     * @OA\Property(
     *      property="due_at",
     *      description="Due at",
     *      type="string",
     *      format="date",
     *      example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *      property="date",
     *      description="Date",
     *      type="string",
     *      format="date",
     *      example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *      property="total_amount",
     *      description="Sum of amount of all invoice items with taxes",
     *      type="string",
     *      example="2,500.20",
     * ),
     * @OA\Property(
     *      property="paid_status",
     *      description="Invoice paid status (paid or not)",
     *      type="string",
     *      enum={"paid","unpaid"},
     * ),
     * @OA\Property(
     *      property="job",
     *      type="object",
     *      nullable=true,
     *      @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="Job identifier",
     *          example="1"
     *      ),
     *      @OA\Property(
     *          property="claim_number",
     *          type="string",
     *          description="Claim number",
     *          example="#10198747-MEL"
     *      ),
     * ),
     * @OA\Property(
     *      property="payments",
     *      type="array",
     *      @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              type="integer",
     *              description="Payment identifier",
     *              example="1"
     *          ),
     *          @OA\Property(
     *              property="type",
     *              type="string",
     *              description="Payment type",
     *              example="credit_card"
     *          ),
     *          @OA\Property(
     *              property="reference",
     *              type="reference",
     *              description="Reference",
     *              example="Payment"
     *          ),
     *          @OA\Property(
     *              property="paid_at",
     *              type="string",
     *              description="Paid at date",
     *              format="date",
     *              example="2018-11-10T09:10:11Z"
     *          ),
     *          @OA\Property(
     *              property="amount",
     *              type="string",
     *              description="Amount of payment",
     *              example="1,100.00"
     *          ),
     *      ),
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
        $data = [
            'id'                => $this->id,
            'recipient_contact' => $this->recipientContact,
            'recipient_name'    => $this->recipient_name,
            'recipient_address' => $this->recipient_address,
            'location_code'     => $this->location->code,
            'due_at'            => $this->due_at,
            'date'              => $this->date,
            'total_amount'      => round($this->getTotalAmount(), 2),
            'paid_status'       => round($this->getAmountDue(), 2) == 0 ? PaidStatuses::PAID : PaidStatuses::UNPAID,
            'job'               => null,
            'payments'          => [],
        ];

        if (null !== $this->job) {
            $data['job'] = [
                'id'           => $this->job->id,
                'claim_number' => $this->job->claim_number,
            ];
        }

        foreach ($this->payments as $payment) {
            $data['payments'][] = [
                'id'        => $payment->id,
                'type'      => $payment->type,
                'reference' => $payment->reference,
                'paid_at'   => $payment->paid_at,
                'amount'    => round($payment->pivot->amount, 2),
            ];
        }

        return $data;
    }
}
