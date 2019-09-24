<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Components\UsageAndActuals\Resources\EquipmentCategoryResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentCategoryResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class EquipmentCategoryResponse extends ApiOKResponse
{
    protected $resource = EquipmentCategoryResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/EquipmentCategoryResource")
     * @var \App\Components\UsageAndActuals\Resources\EquipmentCategoryResource
     */
    protected $data;
}
