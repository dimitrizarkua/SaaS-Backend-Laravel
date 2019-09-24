<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobReimbursementRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "user_id",
 *         "creator_id",
 *         "date_of_expense",
 *         "document_id",
 *         "description",
 *         "total_amount",
 *         "is_chargeable",
 *     },
 *     @OA\Property(
 *         property="user_id",
 *         description="Payee identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *      @OA\Property(
 *         property="creator_id",
 *         description="Creator identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(property="date_of_expense", type="string", format="date"),
 *     @OA\Property(
 *         property="document_id",
 *         description="Document identifier",
 *         type="integer",
 *         example=1,
 *     ),
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
class CreateJobReimbursementRequest extends ApiRequest
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
            'user_id'         => 'required|integer|exists:users,id',
            'creator_id'      => 'required|integer|exists:users,id',
            'date_of_expense' => 'required|date_format:Y-m-d',
            'document_id'     => 'required|integer|exists:documents,id',
            'description'     => 'required|string',
            'total_amount'    => 'required|numeric',
            'is_chargeable'   => 'required|boolean',
        ];
    }
}
