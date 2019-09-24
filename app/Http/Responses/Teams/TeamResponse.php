<?php

namespace App\Http\Responses\Teams;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class TeamResponse
 *
 * @package App\Http\Responses\Teams
 * @OA\Schema(required={"data"})
 */
class TeamResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Team")
     *
     * @var \App\Components\Teams\Models\Team
     */
    protected $data;
}
