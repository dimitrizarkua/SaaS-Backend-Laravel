<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\AssignedContactResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AssignedContactsListResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class AssignedContactsListResponse extends ApiOKResponse
{
    protected $resource = AssignedContactResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AssignedContactResource")
     * ),
     * @var \App\Components\Contacts\Models\Contact[]
     */
    protected $data;
}
