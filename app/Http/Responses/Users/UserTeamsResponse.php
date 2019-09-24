<?php

namespace App\Http\Responses\Users;

use App\Components\Teams\Resources\UserTeamResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class UserTeamsResponse
 *
 * @package App\Http\Responses\Teams
 *
 * @OA\Schema(required={"data"})
 */
class UserTeamsResponse extends ApiOKResponse
{
    protected $resource = UserTeamResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/UserTeamResource")
     * )
     *
     * @var \App\Components\Teams\Models\Team[]
     */
    protected $data;
}
