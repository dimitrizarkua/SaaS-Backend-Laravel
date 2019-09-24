<?php

namespace App\Http\Responses\Finance;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class AccountTypeListResponse
 *
 * @package App\Http\Responses\Finance
 * @OA\Schema(required={"data"})
 */
class AccountTypeListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AccountType")
     * ),
     * @var \App\Components\Finance\Models\AccountType[]
     */
    protected $data;
}
