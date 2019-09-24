<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;

/**
 * Class VehicleStatusTypeListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class VehicleStatusTypeListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/VehicleStatusType")
     * ),
     * @var \App\Components\Operations\Models\VehicleStatusType[]
     */
    protected $data;
}
