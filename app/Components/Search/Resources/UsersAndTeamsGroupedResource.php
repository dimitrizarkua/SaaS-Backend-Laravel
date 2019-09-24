<?php

namespace App\Components\Search\Resources;

use App\Components\Search\Models\UserAndTeam;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class UsersAndTeamsGroupedResource
 *
 * @package App\Components\Search\Resources
 * @OA\Schema(
 *      required={"users", "teams"}
 * )
 */
class UsersAndTeamsGroupedResource extends JsonResource
{
    /**
     * @OA\Property(
     *      property="users",
     *      type="array",
     *      description="Array of users matched with search terms",
     *      @OA\Items(ref="#/components/schemas/UsersAndTeamsResource")
     * ),
     * @OA\Property(
     *      property="teams",
     *      type="array",
     *      description="Array of teams matched with search terms",
     *      @OA\Items(ref="#/components/schemas/UsersAndTeamsResource")
     * )
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
        $result = collect($this->resource)
            ->groupBy('type');

        return [
            'users' => UsersAndTeamsResource::collection($result->get(UserAndTeam::TYPE_USER, collect())),
            'teams' => UsersAndTeamsResource::collection($result->get(UserAndTeam::TYPE_TEAM, collect())),
        ];
    }
}
