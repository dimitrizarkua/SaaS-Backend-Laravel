<?php

namespace App\Components\Locations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class LocationUserResource
 *
 * @package App\Components\Locations\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class LocationUserResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="primary",
     *     description="Defines if this location is primary to the user.",
     *     type="boolean",
     *     example=true,
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
            $result['primary'] = $this['pivot']->getAttribute('primary');
            unset($result['pivot']);
        }

        return $result;
    }
}
