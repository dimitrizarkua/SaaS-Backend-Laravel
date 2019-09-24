<?php

namespace App\Components\Contacts\Resources;

use App\Components\Addresses\Resources\FullAddressResource;
use OpenApi\Annotations as OA;

/**
 * Class ContactAddressListResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/FullAddressResource"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class ContactAddressListResource extends FullAddressResource
{
    /**
     * @OA\Property(property="type", type="string", description="Contact type", example="billing"),
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

        if (isset($result['pivot'])) {
            $result['type'] = $result['pivot']['type'];
            unset($result['pivot']);
        }

        return $result;
    }
}
