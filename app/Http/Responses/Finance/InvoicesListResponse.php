<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\InvoicesListResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoicesListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class InvoicesListResponse extends ApiOKResponse
{
    protected $resource = InvoicesListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/InvoicesListResource")
     * ),
     * @var InvoicesListResource[]
     */
    protected $data;
}
