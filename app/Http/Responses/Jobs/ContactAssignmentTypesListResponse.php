<?php

namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactAssignmentTypesListResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class ContactAssignmentTypesListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobContactAssignmentType")
     * ),
     * @var \App\Components\Jobs\Models\JobContactAssignmentType[]
     */
    protected $data;
}
