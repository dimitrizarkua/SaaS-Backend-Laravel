<?php

namespace App\Components\Finance\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ApproverResource
 *
 * @package App\Components\Finance\Resources
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id", "email", "full_name"}
 * )
 *
 * @mixin \App\Models\User
 */
class ApproverResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="email",
     *     type="string",
     *     example="test@steamatic.com.au"
     * ),
     * @OA\Property(
     *     property="full_name",
     *     type="string",
     *     example="John Smith",
     *     nullable=true
     * ),
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
            'id'        => $this->id,
            'email'     => $this->email,
            'full_name' => $this->full_name,
        ];
    }
}
