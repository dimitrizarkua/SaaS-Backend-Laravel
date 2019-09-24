<?php

namespace App\Http\Responses\Addresses;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class CountryListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Addresses
 */
class CountryListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/Country")
     * )
     * @var \App\Components\Addresses\Models\Country[]
     */
    protected $data;
}
