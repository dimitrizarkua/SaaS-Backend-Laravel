<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreatePurchaseOrderItemRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"gs_code_id", "description", "unit_cost", "quantity", "markup", "gl_account_id", "tax_rate_id"},
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
 *         example=500.15,
 *         minimum=0,
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         description="Quantity",
 *         type="int",
 *         example=1,
 *         minimum=1,
 *     ),
 *     @OA\Property(
 *         property="markup",
 *         description="Markup in percent",
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
class CreatePurchaseOrderItemRequest extends ApiRequest
{
    protected $defaultValues = [
        'markup' => 0.0,
    ];

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
            'gs_code_id'    => 'required|integer|exists:gs_codes,id',
            'description'   => 'required|string',
            'unit_cost'     => 'required|numeric|min:0',
            'quantity'      => 'required|integer|min:1',
            'markup'        => 'numeric|min:0',
            'gl_account_id' => 'required|integer|exists:gl_accounts,id',
            'tax_rate_id'   => 'required|integer|exists:tax_rates,id',
            'position'      => 'integer|min:0',
        ];
    }
}
