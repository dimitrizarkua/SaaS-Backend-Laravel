<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdatePurchaseOrderItemRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(
 *         property="gs_code_id",
 *         description="Identifier of GS code",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="Description of purchase order item",
 *         type="string",
 *         example="Some purchase order item",
 *     ),
 *     @OA\Property(
 *         property="unit_cost",
 *         description="Unit cost",
 *         type="number",
 *         format="float",
 *         example=500.55,
 *         minimum=0,
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         description="Quantity",
 *         type="int",
 *         example=5,
 *         minimum=1,
 *     ),
 *     @OA\Property(
 *         property="markup",
 *         description="Markup",
 *         type="number",
 *         format="float",
 *         example=50.55,
 *         minimum=0,
 *     ),
 *     @OA\Property(
 *         property="gl_account_id",
 *         description="Identifier of GL account",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="tax_rate_id",
 *         description="Identifier of tax rate",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="position",
 *         description="Item position",
 *         type="integer",
 *         example=1,
 *     ),
 * )
 */
class UpdatePurchaseOrderItemRequest extends ApiRequest
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
            'gs_code_id'    => 'integer|exists:gs_codes,id',
            'description'   => 'string',
            'unit_cost'     => 'numeric|min:0',
            'quantity'      => 'integer|min:1',
            'markup'        => 'numeric|min:0',
            'gl_account_id' => 'integer|exists:gl_accounts,id',
            'tax_rate_id'   => 'integer|exists:tax_rates,id',
            'position'      => 'integer|min:0',
        ];
    }
}
