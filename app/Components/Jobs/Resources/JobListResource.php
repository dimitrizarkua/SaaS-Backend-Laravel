<?php

namespace App\Components\Jobs\Resources;

use App\Components\Addresses\Resources\FullAddressResource;
use App\Components\UsageAndActuals\Resources\InsurerContractResource;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobListResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\Job
 *
 * @OA\Schema(
 *     type="object",
 * )
 */
class JobListResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="id",
     *     description="Job identifier.",
     *     type="integer",
     *     example="1"
     * ),
     * @OA\Property(
     *     property="claim_number",
     *     description="Claim number",
     *     type="string",
     *     example="#10198747-MEL"
     * ),
     * @OA\Property(
     *     property="latest_message",
     *     description="Body of latest incoming message or note text of latest added note.",
     *     type="string",
     *     example="Lorem ipsum dolor sit amet.."
     * ),
     * @OA\Property(
     *     property="has_new_replies",
     *     description="Shows whether job has new replies (yellow triangle in GUI).",
     *     type="boolean",
     * ),
     * @OA\Property(
     *     property="touched_at",
     *     description="Shows when job was modified for the last time (for example job reply was received).",
     *     type="string",
     *     format="date-time"
     * ),
     * @OA\Property(
     *     property="pinned_at",
     *     description="Time when job was pinned",
     *     type="string",
     *     format="date-time",
     *     nullable=true,
     * ),
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     example="Clean up water, dry out kitchen cabinetry and timber flooring"
     * ),
     * @OA\Property(
     *     property="next_task",
     *     type="object",
     *     nullable=true,
     *     required={"id","starts_at","ends_at"},
     *     description="Information about next by time task.",
     *     @OA\Property(property="id", type="integer", description="Job task identifier", example=1),
     *     @OA\Property(property="name", type="string", description="Name", example="Customer call"),
     *     @OA\Property(property="due_at", type="string", description="Due at time", format="date-time"),
     *     @OA\Property(property="starts_at", type="string", description="Starts at time", format="date-time"),
     *     @OA\Property(property="ends_at", type="string", description="Ends at time", format="date-time")
     * ),
     * @OA\Property(
     *     property="latest_status",
     *     ref="#/components/schemas/JobStatusResource",
     * ),
     * @OA\Property(
     *     property="site_address",
     *     ref="#/components/schemas/FullAddressResource",
     * ),
     * @OA\Property(
     *     property="insurer",
     *     type="object",
     *     required={"id","full_name"},
     *     description="Contact of issuer assigned to the job.",
     *     @OA\Property(
     *          property="id",
     *          type="integer",
     *          description="Contact identifier.",
     *          example="1",
     *     ),
     *     @OA\Property(
     *          property="contact_name",
     *          type="string",
     *          description="Legal name of company or full name of person (depends on contact type).",
     *          example="John Smith"
     *     )
     * ),
     * @OA\Property(
     *     property="insurer_contract",
     *     ref="#/components/schemas/InsurerContractResource",
     * ),
     * @OA\Property(
     *     property="assigned_location",
     *     ref="#/components/schemas/Location",
     * ),
     * @OA\Property(
     *     property="tags",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Tag")
     * ),
     * @OA\Property(
     *     property="snoozed_until",
     *     description="Shows date until which the job is snoozed",
     *     type="string",
     *     format="date-time",
     *     nullable=true,
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
        $result                  = $this->resource->toArray();
        $result['latest_status'] = JobStatusResource::make($this->latestStatus);

        if ($this->insurer_id) {
            unset($result['insurer_id']);

            $result['insurer'] = [
                'id'           => $this->insurer_id,
                'contact_name' => $this->insurer->getContactName(),
            ];
        }
        if ($this->insurer_contract_id) {
            unset($result['insurer_contract_id']);

            $result['insurer_contract'] = InsurerContractResource::make($this->insurerContract);
        }

        if ($this->nextTask) {
            $result['next_task'] = [
                'id'        => $this->nextTask->id,
                'name'      => $this->nextTask->name,
                'starts_at' => $this->nextTask->starts_at,
                'ends_at'   => $this->nextTask->ends_at,
                'due_at'    => $this->nextTask->due_at,
            ];
        }

        if ($this->assigned_location_id) {
            unset($result['assigned_location_id']);
            $result['assigned_location'] = $this->assignedLocation;
        }

        if ($this->site_address_id) {
            unset($result['site_address_id']);
            $result['site_address'] = FullAddressResource::make($this->siteAddress);
        }

        $tags = $this->tags->toArray();
        foreach ($tags as &$tag) {
            unset($tag['pivot']);
        }
        $result['tags'] = $tags;

        return $result;
    }
}
