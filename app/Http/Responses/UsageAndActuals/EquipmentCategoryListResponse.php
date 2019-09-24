<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Components\UsageAndActuals\Resources\EquipmentCategoryResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentCategoryListResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class EquipmentCategoryListResponse extends ApiOKResponse
{
    protected $resource = EquipmentCategoryResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/EquipmentCategoryResource")
     * ),
     * @var \App\Components\UsageAndActuals\Resources\EquipmentCategoryResource[]
     */
    protected $data;
}
