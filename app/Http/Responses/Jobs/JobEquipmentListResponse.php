<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\FullJobEquipmentResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobEquipmentListResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobEquipmentListResponse extends ApiOKResponse
{
    protected $resource = FullJobEquipmentResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/FullJobEquipmentResource")
     * ),
     * @var \App\Components\Jobs\Resources\FullJobEquipmentResource[]
     */
    protected $data;
}
