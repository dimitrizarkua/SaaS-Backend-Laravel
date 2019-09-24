<?php

namespace App\Http\Responses\SiteSurvey;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class SiteSurveyQuestionOptionListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\SiteSurvey
 */
class SiteSurveyQuestionOptionListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/SiteSurveyQuestionOption")
     * )
     * @var \App\Components\SiteSurvey\Models\SiteSurveyQuestionOption[]
     */
    protected $data;
}
