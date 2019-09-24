<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobSiteSurveyResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\Job
 *
 * @OA\Schema(
 *     type="object",
 * )
 */
class JobSiteSurveyResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="allQuestions",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/SiteSurveyQuestion"),
     * ),
     * @OA\Property(
     *     property="jobQuestions",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobSiteSurveyQuestionResource"),
     * ),
     * @OA\Property(
     *     property="jobRooms",
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/JobRoom"),
     * ),
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $siteSurvey = $this->resource;

        if (!empty($siteSurvey->jobQuestions)) {
            $siteSurvey->jobQuestions = JobSiteSurveyQuestionResource::collection($siteSurvey->jobQuestions);
        }

        return $siteSurvey;
    }
}
