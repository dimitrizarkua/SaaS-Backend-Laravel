<?php

namespace App\Http\Responses\SiteSurvey;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class SiteSurveyQuestionOptionResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\SiteSurvey
 */
class SiteSurveyQuestionOptionResponse extends ApiOKResponse
{
    /**
     * @OA\Property(ref="#/components/schemas/SiteSurveyQuestionOption")
     * @var \App\Components\SiteSurvey\Models\SiteSurveyQuestionOption
     */
    protected $data;
}
