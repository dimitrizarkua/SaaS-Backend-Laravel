<?php

namespace App\Components\Finance\Resources;

use App\Components\Users\Resources\UserProfileMiniResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class FinancialEntityStatusResource
 *
 * @package App\Components\Finance\Resources
 *
 * @mixin \App\Components\Finance\Models\InvoiceStatus
 *
 * @OA\Schema(
 *     type="object",
 *     required={
 *          "user",
 *          "status",
 *          "created_at"
 *     }
 * )
 */
class FinancialEntityStatusResource extends JsonResource
{
    /**
     * @OA\Property(
     *    property="user",
     *    description="User who updated the status",
     *    ref="#/components/schemas/UserProfileMiniResource",
     * ),
     * @OA\Property(
     *    property="status",
     *    ref="#/components/schemas/FinancialEntityStatuses"
     * ),
     * @OA\Property(property="created_at", type="string", format="date-time")
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user'       => UserProfileMiniResource::make($this->user),
            'status'     => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
