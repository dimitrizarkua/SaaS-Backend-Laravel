<?php

namespace App\Components\Operations\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class StaffResource
 *
 * @package App\Components\Operations\Resources
 * @mixin \App\Models\User
 *
 * @OA\Schema(
 *     type="object",
 *     allOf={@OA\Schema(ref="#/components/schemas/User")},
 * )
 */
class StaffResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="week_hours",
     *     description="Total amount of hours scheduled for the week",
     *     type="number",
     *     format="float",
     *     example="30.5"
     * ),
     * @OA\Property(
     *     property="date_hours",
     *     description="Total amount of hours scheduled for the selected date",
     *     type="number",
     *     format="float",
     *     example="8"
     * )
     * @OA\Property(
     *    property="primary_location",
     *    description="Primary location of staff",
     *    ref="#/components/schemas/Location"
     * ),
     * @OA\Property(
     *    property="roles",
     *    description="Roles assigned to staff",
     *    type="array",
     *    @OA\Items(ref="#/components/schemas/Role")
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $result = $this->resource;

        $primaryLocation = $this->primaryLocation()->exists()
            ? $this->primaryLocation()->first()->toArray()
            : null;
        if (null !== $primaryLocation) {
            unset($primaryLocation['pivot']);
        }
        $result['primary_location'] = $primaryLocation;
        $result['roles']            = $this->roles;
        if (isset($result['roles'])) {
            foreach ($result['roles'] as $role) {
                unset($role['pivot']);
            }
        }
        if (isset($result['location_ids'])) {
            unset($result['location_ids']);
        }
        if (isset($result['pivot'])) {
            unset($result['pivot']);
        }

        return $result;
    }
}
