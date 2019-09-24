<?php

namespace App\Components\Search\Resources;

use App\Components\Search\Models\UserAndTeam;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UsersAndTeamsResource
 *
 * @package App\Components\Search\Resources
 * @OA\Schema(
 *      required={"id","name","type"}
 * )
 */
class UsersAndTeamsResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="id",
     *      type="integer",
     *      description="Id of entity",
     *      example=1
     * )
     * @OA\Property(
     *      property="name",
     *      type="string",
     *      description="Name of entity. In case of user will be a user full name. In case of team will be team name",
     *      example="John Smith"
     * )
     * @OA\Property(
     *      property="type",
     *      type="string",
     *      enum={"user","team"},
     *      description="Type of found entity",
     * )
     * @OA\Property(
     *     property="avatar",
     *     description="Full url to avatar image. Only applied to user entities.",
     *     type="string",
     *     nullable=true,
     *     example="http://url-to-photo"
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
        $shouldSetAvatar = UserAndTeam::TYPE_USER === $this->resource['type']
            && null !== $this->resource['avatar_photos_id'];

        $primaryLocation = null;

        if (isset($this->resource['primary_location']) && UserAndTeam::TYPE_USER === $this->resource['type']) {
            $primaryLocation = $this->resource['primary_location'];
        }

        if (null !== $primaryLocation) {
            unset($primaryLocation['pivot']);
        }

        return [
            'id'               => $this->resource['entity_id'],
            'name'             => $this->resource['name'],
            'type'             => $this->resource['type'],
            'avatar'           => $shouldSetAvatar ? $this->resource['avatar']['url'] : null,
            'primary_location' => $primaryLocation,
        ];
    }
}
