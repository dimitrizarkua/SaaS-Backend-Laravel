<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Components\UsageAndActuals\Resources\EquipmentResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class EquipmentResponse extends ApiOKResponse
{
    protected $resource = EquipmentResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/EquipmentResource")
     * @var \App\Components\UsageAndActuals\Resources\EquipmentResource
     */
    protected $data;
}
