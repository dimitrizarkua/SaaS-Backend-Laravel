<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\StaffResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class StaffResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class StaffResponse extends ApiOKResponse
{
    protected $resource = StaffResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/StaffResource"
     * )
     *
     * @var \App\Components\Operations\Resources\StaffResource
     */
    protected $data;
}
