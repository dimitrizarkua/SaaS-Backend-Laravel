<?php

namespace App\Components\Finance\Resources;

use App\Components\Finance\Models\FinancialEntityItem;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FinancialEntityItemResource
 *
 * @package App\Components\Finance\Resources
 *
 * @property FinancialEntityItem $resource
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "gl_account",
 *          "tax_rate",
 *          "gs_code",
 *          "total_amount",
 *          "tax",
 *     }
 * )
 */
class FinancialEntityItemResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="gl_account",
     *     ref="#/components/schemas/GLAccountResource"
     * ),
     * @OA\Property(
     *     property="tax_rate",
     *     ref="#/components/schemas/TaxRate"
     * ),
     * @OA\Property(
     *     property="gs_code",
     *     ref="#/components/schemas/GSCode"
     * ),
     * @OA\Property(
     *      property="total_amount",
     *      description="Total amount of item",
     *      type="number",
     *      example=1500.00
     * ),
     * @OA\Property(
     *     property="tax",
     *     description="Tax amount of item (Tax should be calculated only for GST on Income)",
     *     type="number",
     *     example=1.5
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

        $result['gl_account']   = $this->resource->glAccount;
        $result['tax_rate']     = $this->resource->taxRate;
        $result['gs_code']      = $this->resource->gsCode;
        $result['total_amount'] = $this->resource->getSubTotal();
        $result['tax']          = $this->resource->getItemTax();

        return $result;
    }
}
