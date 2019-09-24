<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderSuggestedApproverResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id", "email", "first_name", "last_name", "full_name", "purchase_order_approve_limit"}
 * )
 *
 * @mixin \App\Models\User
 */
class PurchaseOrderSuggestedApproverResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="id",
     *      description="User id",
     *      type="id",
     *      example="1"
     * ),
     * @OA\Property(
     *      property="first_name",
     *      description="First name of user",
     *      type="string",
     *      example="John"
     * ),
     * @OA\Property(
     *      property="last_name",
     *      description="Last name of user",
     *      type="string",
     *      example="Smith"
     * ),
     * @OA\Property(
     *      property="full_name",
     *      description="Full name of user",
     *      type="string",
     *      example="John Smith"
     * ),
     * @OA\Property(
     *      property="email",
     *      description="Email of user",
     *      type="string",
     *      example="test@steamatic.com"
     * ),
     * @OA\Property(
     *      property="purchase_order_approve_limit",
     *      description="Approve limit for purchase orders of user",
     *      type="number",
     *      example="1000.00"
     * ),
     */
}
