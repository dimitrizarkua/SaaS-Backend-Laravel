<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoicesInfoResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class InvoicesInfoResponse extends ApiOKResponse
{

    /**
     * @OA\Schema(
     *     schema="InvoiceCounter",
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
     *     required={"draft","pending_approval","approved","paid"},
     *     @OA\Property(
     *         property="draft",
     *         description="Counter of invoices with draft status",
     *         ref="#/components/schemas/InvoiceCounter"
     *     ),
     *     @OA\Property(
     *         property="unpaid",
     *         ref="#/components/schemas/InvoiceCounter",
     *         description="Count of unpaid invoices",
     *     ),
     *     @OA\Property(
     *         property="overdue",
     *         ref="#/components/schemas/InvoiceCounter",
     *         description="Count of overdue invoices",
     *     ),
     * ),
     * @var \App\Components\Finance\Resources\InvoiceResource
     */
    protected $data;
}
