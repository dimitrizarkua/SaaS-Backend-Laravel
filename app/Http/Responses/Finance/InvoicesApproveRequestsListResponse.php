<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\InvoicesApproveRequestsResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoicesApproveRequestsListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class InvoicesApproveRequestsListResponse extends ApiOKResponse
{
    protected $resource = InvoicesApproveRequestsResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/InvoicesApproveRequestsResource")
     * ),
     * @var InvoicesApproveRequestsResource[]
     */
    protected $data;
}
