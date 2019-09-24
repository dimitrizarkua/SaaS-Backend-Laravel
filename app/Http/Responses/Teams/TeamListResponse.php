<?php

namespace App\Http\Responses\Teams;

use App\Http\Responses\ApiOKResponse;

/**
 * Class TeamListResponse
 *
 * @package App\Http\Responses\Teams
 * @OA\Schema(required={"data"})
 */
class TeamListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Team")
     * )
     *
     * @var \App\Components\Teams\Models\Team[]
     */
    protected $data;
}
