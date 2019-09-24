<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\FullJobEquipmentResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class FullJobEquipmentResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class FullJobEquipmentResponse extends ApiOKResponse
{
    protected $resource = FullJobEquipmentResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/FullJobEquipmentResource")
     * @var \App\Components\Jobs\Resources\FullJobEquipmentResource
     */
    protected $data;
}
