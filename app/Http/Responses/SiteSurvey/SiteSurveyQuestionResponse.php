<?php

namespace App\Http\Responses\SiteSurvey;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class SiteSurveyQuestionResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\SiteSurvey
 */
class SiteSurveyQuestionResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/SiteSurveyQuestion")
     * @var \App\Components\SiteSurvey\Models\SiteSurveyQuestion
     */
    protected $data;
}
