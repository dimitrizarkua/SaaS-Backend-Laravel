<?php

namespace App\Http\Requests\Finance;

use OpenApi\Annotations as OA;
use App\Http\Requests\ApiRequest;

/**
 * Class CreateApprovalRequestRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"approver_list"},
 *     @OA\Property(
 *         property="approver_list",
 *         description="A list of approver ids",
 *         type="array",
 *         @OA\Items(
 *              type="integer",
 *              description="Id of approver",
 *              example="1"
 *         )
 *     ),
 * )
 *
 */
class CreateApprovalRequestRequest extends ApiRequest
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
            'approver_list'   => 'required|array',
            'approver_list.*' => 'integer|exists:users,id',
        ];
    }

    /**
     * @return array
     */
    public function getApproverIdsList(): array
    {
        return $this->input('approver_list');
    }
}
