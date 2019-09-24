<?php

namespace App\Components\Teams\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UserTeamResource
 *
 * @package App\Components\Teams\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class UserTeamResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="data",
     *     description="Defines collection of the teams related to user",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Team"),
     * )
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
        $result = $this->resource->toArray();

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
