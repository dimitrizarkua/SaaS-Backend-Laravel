<?php

namespace App\Components\Contacts\Resources;

use App\Components\Notes\Models\Note;
use OpenApi\Annotations as OA;

/**
 * Class ContactResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Contact"),
 *         @OA\Schema(ref="#/components/schemas/BaseContactResource"),
 *     },
 * )
 *
 * @mixin \App\Components\Contacts\Models\Contact
 * @package App\Components\Contacts\Resources
 */
class ContactResource extends BaseContactResource
{
    /**
     * @OA\Property(
     *     property="contact_status",
     *     ref="#/components/schemas/ContactStatus"
     * ),
     * @OA\Property(
     *     property="contact_category",
     *     ref="#/components/schemas/ContactCategory"
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
        $result['contact_status']   = $this->latestStatus->toArray();
        $result['contact_category'] = $this->category->toArray();

        if (isset($this->pivot) && $this->pivot->pivotParent instanceof Note) {
            $result['meeting_id'] = $result['pivot']['meeting_id'];
        }

        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        if (!empty($result['addresses'])) {
            $result['addresses'] = ContactAddressListResource::collection($this->addresses);
        }

        return $result;
    }
}
