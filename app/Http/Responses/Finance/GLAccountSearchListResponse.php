<?php

namespace App\Http\Responses\Finance;

use App\Components\Finance\Resources\GLAccountSearchListResource;
use App\Http\Responses\ApiOKResponse;

/**
 * Class GLAccountSearchListResponse
 *
 * @package App\Http\Responses\Finance
 *
 * @OA\Schema(required={"data"})
 */
class GLAccountSearchListResponse extends ApiOKResponse
{
    protected $resource = GLAccountSearchListResource::class;

    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/GLAccountSearchListResource")
     * )
     */
    protected $data;
}
