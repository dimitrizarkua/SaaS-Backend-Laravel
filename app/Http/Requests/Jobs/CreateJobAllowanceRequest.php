<?php

namespace App\Http\Requests\Jobs;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreateJobAllowanceRequest
 *
 * @package App\Http\Requests\Jobs
 * @OA\Schema(
 *     type="object",
 *     required={
 *         "job_id",
 *         "user_id",
 *         "creator_id",
 *         "allowance_type_id",
 *         "date_given",
 *         "amount",
 *     },
 *     @OA\Property(
 *         property="job_id",
 *         description="Job identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         description="Payee identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="creator_id",
 *         description="Creator identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(
 *         property="allowance_type_id",
 *         description="Allownace type identifier",
 *         type="integer",
 *         example=1,
 *     ),
 *     @OA\Property(property="date_given", type="string", format="date-time"),
 *     @OA\Property(
 *         property="amount",
 *         description="Amount",
 *         type="integer",
 *         example=1
 *     ),
 * )
 */
class CreateJobAllowanceRequest extends ApiRequest
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
            'job_id'            => 'required|integer|exists:jobs,id',
            'user_id'           => 'required|integer|exists:users,id',
            'creator_id'        => 'required|integer|exists:users,id',
            'allowance_type_id' => 'required|integer|exists:allowance_types,id',
            'date_given'        => 'required|date_format:Y-m-d',
            'amount'            => 'required|integer',
        ];
    }
}
