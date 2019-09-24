<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class AddInvoiceItemRequest
 *
 * @package App\Http\Requests\Finance
 * @OA\Schema(
 *     type="object",
 *     required={"gs_code_id", "description", "unit_cost", "quantity", "gl_account_id", "tax_rate_id"},
 *     @OA\Property(
 *         property="gs_code_id",
 *         description="Item GS code identifier",
 *         type="integer",
 *         example=2,
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="Description",
 *         type="string",
 *         example="General Restoration Technician labour (hours)",
 *     ),
 *     @OA\Property(
 *         property="unit_cost",
 *         description="Cost of one unit",
 *         type="number",
 *         format="float",
 *         example=58.00,
 *         minimum=0,
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         description="Quantity of units in the invoice item",
 *         type="integer",
 *         example=5,
 *         minimum=1,
 *     ),
 *     @OA\Property(
 *         property="discount",
 *         description="Discount for one unit in procent",
 *         type="number",
 *         format="float",
 *         example=35.88,
 *         minimum=0,
 *         maximum=100,
 *     ),
 *     @OA\Property(
 *         property="gl_account_id",
 *         description="GL Accoutn identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="tax_rate_id",
 *         description="Tax Rate identifier",
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
class AddInvoiceItemRequest extends ApiRequest
{
    protected $defaultValues = [
        'discount' => 0.0,
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
            'discount'      => 'nullable|numeric|min:0|max:100',
            'gl_account_id' => 'required|integer|exists:gl_accounts,id',
            'tax_rate_id'   => 'required|integer|exists:tax_rates,id',
            'position'      => 'integer|min:0',
        ];
    }
}
