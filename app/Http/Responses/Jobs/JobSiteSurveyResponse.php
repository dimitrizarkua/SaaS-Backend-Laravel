<?php

namespace App\Http\Responses\Jobs;

use App\Components\Jobs\Resources\JobSiteSurveyResource;
use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobSiteSurveyResponse
 *
 * @package App\Http\Responses\Jobs
 *
 * @OA\Schema(required={"data"})
 */
class JobSiteSurveyResponse extends ApiOKResponse
{
    protected $resource = JobSiteSurveyResource::class;

    /**
     * @OA\Property(
     *     ref="#/components/schemas/JobSiteSurveyResource"
     * )
     *
     * @var JobSiteSurveyResource
     */
    protected $data;
}
