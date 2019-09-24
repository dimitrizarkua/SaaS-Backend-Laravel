<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\ApiRequest;
use OpenApi\Annotations as OA;

/**
 * Class CreatePurchaseOrderApproveRequestsRequest
 *
 * @package App\Http\Requests\Finance
 *
 * @OA\Schema(
 *     type="object",
 *     required={"approver_ids"},
 *     @OA\Property(
 *        property="approver_ids",
 *        description="Identifiers of users who can approve request",
 *        type="array",
 *        @OA\Items(
 *            type="integer",
 *            example=1
 *        ),
 *     ),
 * )
 */
class CreatePurchaseOrderApproveRequestsRequest extends ApiRequest
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
            'approver_ids'   => 'required|array',
            'approver_ids.*' => 'integer|exists:users,id',
        ];
    }

    /**
     * @return array
     */
    public function getApprovers(): array
    {
        return $this->get('approver_ids');
    }
}
