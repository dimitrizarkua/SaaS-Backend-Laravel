<?php

namespace App\Components\Contacts\Resources;

use OpenApi\Annotations as OA;

/**
 * Class ConciseContactResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/BaseContactResource"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class ConciseContactResource extends BaseContactResource
{
    /**
     * @OA\Property(
     *     property="tags",
     *     type="array",
     *     description="Contact tags",
     *     @OA\Items(ref="#/components/schemas/ContactTagListResource")
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
        $result = parent::toArray($request);

        $result['tags'] = ContactTagListResource::collection($this->tags);

        return $result;
    }
}
