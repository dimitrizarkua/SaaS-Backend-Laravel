<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobEquipmentTotalAmountResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class JobEquipmentTotalAmountResponse
 *
 * @package App\Http\Responses\Jobs
 * @OA\Schema(required={"data"})
 */
class JobEquipmentTotalAmountResponse extends ApiOKResponse
{
    protected $resource = JobEquipmentTotalAmountResource::class;

    /**
     * @OA\Property(ref="#/components/schemas/JobEquipmentTotalAmountResource")
     * @var \App\Components\Jobs\Resources\JobEquipmentTotalAmountResource
     */
    protected $data;
}
