<?php

namespace App\Http\Responses\UsageAndActuals;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class EquipmentListResponse
 *
 * @package App\Http\Responses\UsageAndActuals
 * @OA\Schema(required={"data"})
 */
class EquipmentListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Equipment")
     * ),
     * @var \App\Components\UsageAndActuals\Models\Equipment[]
     */
    protected $data;
}
