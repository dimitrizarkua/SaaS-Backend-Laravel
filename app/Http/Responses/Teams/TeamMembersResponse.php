<?php

namespace App\Http\Responses\Teams;

use App\Components\Teams\Resources\TeamMemberResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class TeamMembersResponse
 *
 * @package App\Http\Responses\Teams
 *
 * @OA\Schema(required={"data"})
 */
class TeamMembersResponse extends ApiOKResponse
{
    protected $resource = TeamMemberResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/TeamMemberResource")
     * )
     *
     * @var \App\Models\User[]
     */
    protected $data;
}
