<?php


namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteInfoResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class CreditNoteInfoResponse extends ApiOKResponse
{
    /**
     * @OA\Schema(
     *     schema="CreditNoteCounter",
     *     type="object",
     *     @OA\Property(
     *          property="count",
     *          type="integer",
     *          description="Count of items in current catgory",
     *          example="3",
     *     ),
     *     @OA\Property(
     *          property="amount",
     *          type="number",
     *          description="Total amount of all items in current category",
     *          example="3",
     *     ),
     * ),
     *
     * @OA\Property(
     *     type="object",
     *     required={"draft","pending_approval","approved"},
     *     @OA\Property(
     *         property="draft",
     *         ref="#/components/schemas/CreditNoteCounter",
     *         description="Counter of invoices with draft status",
     *     ),
     *     @OA\Property(
     *         property="pending_approval",
     *         ref="#/components/schemas/CreditNoteCounter",
     *         description="Count of  invoices",
     *     ),
     *     @OA\Property(
     *         property="approved",
     *         ref="#/components/schemas/CreditNoteCounter",
     *         description="Count of overdue invoices",
     *     ),
     * ),
     */
    protected $data;
}
