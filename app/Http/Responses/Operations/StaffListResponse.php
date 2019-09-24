<?php

namespace App\Http\Responses\Operations;

use App\Components\Operations\Resources\StaffResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class StaffListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class StaffListResponse extends ApiOKResponse
{
    protected $resource = StaffResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/StaffResource")
     * )
     *
     * @var \App\Components\Operations\Resources\StaffResource[]
     */
    protected $data;
}
