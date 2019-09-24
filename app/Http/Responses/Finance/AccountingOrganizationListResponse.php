<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\AccountingOrganizationResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AccountingOrganizationListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class AccountingOrganizationListResponse extends ApiOKResponse
{
    protected $resource = AccountingOrganizationResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AccountingOrganizationResource")
     * ),
     * @var \App\Components\Finance\Resources\AccountingOrganizationResource[]
     */
    protected $data;
}
