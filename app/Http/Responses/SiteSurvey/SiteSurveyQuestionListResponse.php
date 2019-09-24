<?php

namespace App\Http\Responses\SiteSurvey;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class SiteSurveyQuestionListResponse
 *
 * @OA\Schema(required={"data"})
 * @package App\Http\Responses\SiteSurvey
 */
class SiteSurveyQuestionListResponse extends ApiOKResponse
{
    /**
     * @OA\Property(
     *     property="data",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/SiteSurveyQuestion")
     * )
     * @var \App\Components\SiteSurvey\Models\SiteSurveyQuestion[]
     */
    protected $data;
}
