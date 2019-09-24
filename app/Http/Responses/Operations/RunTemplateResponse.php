<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class RunTemplateResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class RunTemplateResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobRunTemplate")
     * @var \App\Components\Operations\Models\JobRunTemplate
     */
    protected $data;
}
