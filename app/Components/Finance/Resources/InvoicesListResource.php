<?php

namespace App\Components\Finance\Resources;

use App\Components\Finance\Models\Invoice;
use OpenApi\Annotations as OA;

/**
 * Class InvoicesListResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     required={"due_at","balance_due","virtual_status"},
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/FinanceEntityListResource")
 *     }
 * )
 *
 * @property Invoice $resource
 */
class InvoicesListResource extends FinanceEntityListResource
{
    /**
     * @OA\Property(
     *     property="due_at",
     *     description="Due at",
     *     type="string",
     *     format="date-time",
     *     example="2018-11-10T09:10:11Z"
     * ),
     * @OA\Property(
     *    property="balance_due",
     *    description="Balance due",
     *    type="number",
     *    example=1456.00,
     * ),
     * @OA\Property(
     *    property="virtual_status",
     *    ref="#/components/schemas/InvoiceVirtualStatuses"
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        $data                = parent::toArray($request);
        $data['balance_due'] = $this->resource->getAmountDue();
        $data['due_at']      = $this->resource->due_at;

        return $data;
    }
}
