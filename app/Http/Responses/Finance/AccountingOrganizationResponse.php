<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\AccountingOrganizationResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AccountingOrganizationResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class AccountingOrganizationResponse extends ApiOKResponse
{
    protected $resource = AccountingOrganizationResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/AccountingOrganizationResource"
     * ),
     * @var \App\Components\Finance\Resources\AccountingOrganizationResource
     */
    protected $data;
}
