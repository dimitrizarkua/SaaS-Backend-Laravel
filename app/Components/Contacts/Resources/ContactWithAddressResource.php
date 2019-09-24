<?php

namespace App\Components\Contacts\Resources;

use App\Components\Addresses\Resources\FullAddressResource;
use OpenApi\Annotations as OA;

/**
 * Class ContactWithAddressResource
 *
 * @package App\Components\Contacts\Resources
 * @mixin \App\Components\Contacts\Models\Contact
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ContactResource"),
 *     },
 * )
 */
class ContactWithAddressResource extends ContactResource
{
    /**
     * @OA\Property(
     *     property="mailing_address",
     *     ref="#/components/schemas/FullAddressResource",
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
        $result                    = parent::toArray($request);
        $result['mailing_address'] = FullAddressResource::make($this->getMailingAddress());

        return $result;
    }
}
