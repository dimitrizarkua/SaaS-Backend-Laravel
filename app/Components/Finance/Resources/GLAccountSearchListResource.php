<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class GLAccountSearchListResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id", "name", "code", "is_bank_account", "enable_payments_to_account", "location", "account_type",
 *     "accounting_organization", "tax_rate"},
 * )
 */
class GLAccountSearchListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     description="GL account identifier.",
     *     example=1,
     * ),
     * @OA\Property(
     *     property="name",
     *     type="string",
     *     description="GL account name.",
     *     example="Cash in Bank"
     * ),
     * @OA\Property(
     *     property="code",
     *     type="string",
     *     nullable=true,
     *     description="GL account code.",
     *     example="1010"
     * ),
     * @OA\Property(
     *     property="is_bank_account",
     *     type="boolean",
     *     description="Indicates whether bank account number was set",
     *     example=true
     * ),
     * @OA\Property(
     *     property="enable_payments_to_account",
     *     type="boolean",
     *     description="Indicates whether payments enabled for the account",
     *     example=true
     * ),
     * @OA\Property(
     *     property="location",
     *     type="object",
     *     required={"id","name"},
     *     description="Location",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Location identifier.",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Location name.",
     *         example="Canberra"
     *     ),
     * ),
     * @OA\Property(
     *     property="account_type",
     *     type="object",
     *     required={"id","name"},
     *     description="Account type",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Account type identifier.",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Account type name.",
     *         example="Revenue - Other Income"
     *     ),
     * ),
     * @OA\Property(
     *     property="accounting_organization",
     *     type="object",
     *     required={"id"},
     *     description="Accounting organization.",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Accounting organization identifier.",
     *         example=1
     *     ),
     * ),
     * @OA\Property(
     *     property="tax_rate",
     *     type="object",
     *     required={"id","name","value"},
     *     description="Tax rate",
     *     @OA\Property(
     *         property="id",
     *         type="integer",
     *         description="Tax rate identifier.",
     *         example=1
     *     ),
     *     @OA\Property(
     *         property="name",
     *         type="string",
     *         description="Tax rate name.",
     *         example="GST on Income"
     *     ),
     *     @OA\Property(
     *         property="value",
     *         type="number",
     *         format="float",
     *         example=0.1
     *     )
     * )
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result         = [];
        $result['id']   = $this->resource->gl_account_id;
        $result['name'] = $this->resource->gl_account_name;
        $result['code'] = $this->resource->gl_account_code;

        $result['location'] = [
            'id'   => $this->resource->location_id,
            'name' => $this->resource->location_name,
        ];

        $result['account_type'] = [
            'id'   => $this->resource->account_type_id,
            'name' => $this->resource->account_type_name,
        ];

        $result['accounting_organization'] = [
            'id' => $this->resource->accounting_organization_id,
        ];

        $result['is_bank_account'] = $this->resource->is_bank_account;

        $result['enable_payments_to_account'] = $this->resource->enable_payments_to_account;

        $result['tax_rate'] = [
            'id'    => $this->resource->tax_rate_id,
            'name'  => $this->resource->tax_rate_name,
            'value' => $this->resource->tax_rate_value,
        ];

        return $result;
    }
}
