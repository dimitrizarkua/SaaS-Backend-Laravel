<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;

/**
 * Class VehicleListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class VehicleListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Vehicle")
     * ),
     * @var \App\Components\Operations\Models\Vehicle[]
     */
    protected $data;
}
