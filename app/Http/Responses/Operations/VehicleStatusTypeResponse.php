<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class VehicleStatusTypeResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class VehicleStatusTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/VehicleStatusType")
     * @var \App\Components\Operations\Models\VehicleStatusType
     */
    protected $data;
}
