<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AccountTypeResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class AccountTypeResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     ref="#/components/schemas/AccountType"
     * ),
     * @var \App\Components\Finance\Models\AccountType
     */
    protected $data;
}
