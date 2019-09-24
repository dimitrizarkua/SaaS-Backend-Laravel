<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class UpdateJobReimbursementRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     @OA\Property(property="date_of_expense", type="string", format="date"),
 *     @OA\Property(
 *         property="description",
 *         description="Description",
 *         type="string",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="total_amount",
 *         description="Total amount",
 *         type="number",
 *         format="float",
 *         example=12.3
 *     ),
 *     @OA\Property(
 *         property="is_chargeable",
 *         description="Is current reimbursment chargeable",
 *         type="boolean",
 *     ),
 * )
 */
class UpdateJobReimbursementRequest extends ApiRequest
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
            'date_of_expense' => 'date_format:Y-m-d',
            'description'     => 'string',
            'total_amount'    => 'numeric',
            'is_chargeable'   => 'boolean',
        ];
    }
}
