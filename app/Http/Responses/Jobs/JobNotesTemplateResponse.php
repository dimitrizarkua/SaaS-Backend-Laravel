<?php
namespace App\Http\Responses\Jobs;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobNotesTemplateResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\Jobs
 */
class JobNotesTemplateResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/JobNotesTemplate")
     * @var \App\Components\Jobs\Models\JobNotesTemplate
     */
    protected $data;
}
