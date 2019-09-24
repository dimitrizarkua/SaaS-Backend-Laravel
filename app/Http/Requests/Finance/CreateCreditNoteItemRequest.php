<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateCreditNoteItemRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id", "gs_code_id","description","quantity","unit_cost","gl_account_id","tax_rate_id"},
 *     @OA\Property(
 *         property="gs_code_id",
 *         description="Identifier of a GS code",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="description",
 *         description="Credit note item description",
 *         type="string",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         description="The number of units in item",
 *         type="int",
 *         example=1,
 *         minimum=1,
 *     ),
 *     @OA\Property(
 *         property="unit_cost",
 *         description="Single unit cost",
 *         type="number",
 *         format="float",
 *         example=1.5,
 *         minimum=0,
 *     ),
 *     @OA\Property(
 *         property="gl_account_id",
 *         description="Identifier of a GL account",
 *         type="integer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="tax_rate_id",
 *         description="Identifier of a tax rate",
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
class CreateCreditNoteItemRequest extends ApiRequest
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
            'gs_code_id'    => 'required|integer|exists:gs_codes,id',
            'description'   => 'required|string',
            'quantity'      => 'required|integer|min:1',
            'unit_cost'     => 'required|numeric|min:0',
            'gl_account_id' => 'required|integer|exists:gl_accounts,id',
            'tax_rate_id'   => 'required|integer|exists:tax_rates,id',
            'position'      => 'integer|min:0',
        ];
    }
}
