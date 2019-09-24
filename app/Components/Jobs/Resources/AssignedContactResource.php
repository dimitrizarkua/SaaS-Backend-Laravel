<?php

namespace App\Components\Jobs\Resources;

use App\Components\Contacts\Resources\ConciseContactResource;
use App\Components\Contacts\Resources\ContactAddressListResource;
use OpenApi\Annotations as OA;

/**
 * Class AssignedContactResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Contacts\Models\Contact
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/ConciseContactResource")},
 * )
 */
class AssignedContactResource extends ConciseContactResource
{
    /**
     * @OA\Property(
     *     property="addresses",
     *     description="List of known addresses assigned to this contact.",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/ContactAddressListResource")
     * )
     * @OA\Property(
     *     property="invoice_to",
     *     description="Defines whether this contact should be invoiced or not.",
     *     type="boolean",
     *     example=false,
     * )
     * @OA\Property(
     *     property="assigned_at",
     *     description="Defines date when this contact was assigned to the job.",
     *     type="string",
     *     format="date-time"
     * )
     * @OA\Property(
     *     property="assignment_type",
     *     description="Defines assignment type of the contact.",
     *     ref="#/components/schemas/JobContactAssignmentType"
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
        $result = parent::toArray($request);

        $result['addresses'] = ContactAddressListResource::collection($this->addresses);
        if (isset($result['pivot'])) {
            $result['invoice_to']             = $this['pivot']->getAttribute('invoice_to');
            $result['assigned_at']            = $this['pivot']->getAttribute('created_at');
            $result['job_assignment_type_id'] = $this['pivot']->getAttribute('job_assignment_type_id');

            foreach ($result['assignment_types'] as $type) {
                if ($type['pivot']['job_assignment_type_id'] === $result['job_assignment_type_id']) {
                    unset($type['pivot']);
                    $result['assignment_type'] = $type;
                    break;
                }
            }

            unset(
                $result['pivot'],
                $result['assignment_types'],
                $result['job_assignment_type_id']
            );
        }

        return $result;
    }
}
