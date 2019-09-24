<?php

namespace App\Components\Contacts\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ContactTagListResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Tag"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class ContactTagListResource extends JsonResource
{
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
