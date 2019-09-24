<?php

namespace App\Http\Responses\Operations;

use App\Http\Responses\ApiOKResponse;

/**
 * Class RunTemplateListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Operations
 */
class RunTemplateListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobRunTemplate")
     * ),
     * @var \App\Components\Operations\Models\JobRunTemplate[]
     */
    protected $data;
}
