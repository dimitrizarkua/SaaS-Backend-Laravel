<?php

namespace App\Components\Contacts\Resources;

use App\Components\Addresses\Models\Country;
use App\Components\Contacts\Models\Enums\ContactTypes;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class ContactListResource
 *
 * @OA\Schema(
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ContactResource"),
 *     },
 * )
 *
 * @package App\Components\Contacts\Resources
 */
class ContactListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="has_alerts",
     *     type="boolean",
     *     description="Indicates if the contact has alert tags",
     *     example="true"
     * ),
     * @OA\Property(
     *     property="addresses",
     *     description="A list of contact addresses",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Address"),
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
        $result = $this->resource['data'];

        if (ContactTypes::PERSON === $result['contact_type']) {
            unset($result['legal_name']);
            unset($result['trading_name']);
            unset($result['abn']);
            unset($result['website']);
            unset($result['default_payment_terms_days']);
        } else {
            unset($result['first_name']);
            unset($result['last_name']);
            unset($result['job_title']);
            unset($result['direct_phone']);
            unset($result['mobile_phone']);
        }
        unset($result['contact_status_name']);
        unset($result['contact_category_name']);

        foreach ($result['addresses'] as &$address) {
            $country = Country::find($address['suburb']['state']['country_id']);
            $address['suburb']['state']['country'] = $country ? $country->toArray() : [];
            $address['type'] = $address['pivot']['type'];
            unset($address['pivot']);
            unset($address['suburb_id']);
            unset($address['suburb']['state_id']);
            unset($address['suburb']['state']['country_id']);
        }

        return $result;
    }
}
