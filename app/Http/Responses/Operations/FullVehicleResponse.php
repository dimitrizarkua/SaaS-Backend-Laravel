<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullVehicleResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class FullVehicleResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/Vehicle")
     * @var \App\Components\Operations\Models\Vehicle
     */
    protected $data;
}
