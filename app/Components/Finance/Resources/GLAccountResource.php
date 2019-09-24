<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountResource
 *
 * @package App\Components\Finance\Resources
 * @mixin \App\Components\Finance\Models\GLAccount
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/GLAccount")},
 * )
 */
class GLAccountResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="account_type",
     *     ref="#/components/schemas/AccountType"
     * ),
     * @OA\Property(
     *     property="tax_rate",
     *     ref="#/components/schemas/TaxRate"
     * )
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

        $result['account_type'] = $this->accountType->toArray();

        $result['tax_rate'] = null;
        if (isset($this->taxRate)) {
            $result['tax_rate'] = $this->taxRate->toArray();
        }

        return $result;
    }
}
