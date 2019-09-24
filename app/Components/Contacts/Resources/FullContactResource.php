<?php

namespace App\Components\Contacts\Resources;

use OpenApi\Annotations as OA;

/**
 * Class FullContactResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ContactResource"),
 *     },
 * )
 *
 * @mixin \App\Components\Contacts\Models\Contact
 * @package App\Components\Contacts\Resources
 */
class FullContactResource extends ContactResource
{
    /**
     * @OA\Property(
     *     property="notes",
     *     type="array",
     *     description="Contact notes",
     *     @OA\Items(ref="#/components/schemas/ContactNoteListResource")
     * ),
     * @OA\Property(
     *     property="addresses",
     *     type="array",
     *     description="Contact addresses",
     *     @OA\Items(ref="#/components/schemas/ContactAddressListResource")
     * ),
     * @OA\Property(
     *     property="tags",
     *     type="array",
     *     description="Contact tags",
     *     @OA\Items(ref="#/components/schemas/ContactTagListResource")
     * ),
     * @OA\Property(
     *     property="subsidiaries",
     *     type="array",
     *     description="Contact subsidiaries",
     *     @OA\Items(ref="#/components/schemas/ContactResource")
     * ),
     * @OA\Property(
     *     property="parent_company",
     *     description="Head office / parent company of the contact",
     *     ref="#/components/schemas/ContactResource"
     * ),
     * @OA\Property(
     *     property="managed_accounts",
     *     description="Staff members attached to the contact",
     *     ref="#/components/schemas/User"
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
        $result                     = parent::toArray($request);
        $result['notes']            = ContactNoteListResource::collection($this->notes);
        $result['addresses']        = ContactAddressListResource::collection($this->addresses);
        $result['tags']             = ContactTagListResource::collection($this->tags);
        $result['subsidiaries']     = ContactResource::collection($this->subsidiaries);
        $result['parent_company']   = ContactResource::make($this->getParentCompany());
        $result['managed_accounts'] = $this->managedAccounts;

        return $result;
    }
}
