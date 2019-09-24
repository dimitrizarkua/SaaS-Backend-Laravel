<?php

namespace App\Components\Jobs\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * Class JobSiteSurveyQuestionResource
 *
 * @package App\Components\Jobs\Resources
 * @mixin \App\Components\Jobs\Models\Job
 *
 * @OA\Schema(
 *     type="object",
 *     required={"site_survey_question_id", "job_id"}
 * )
 */
class JobSiteSurveyQuestionResource extends JsonResource
{
    /**
     * @OA\Property(
     *     property="site_survey_question_id",
     *     type="integer",
     *     example="1",
     * ),
     * @OA\Property(
     *     property="site_survey_question_option_id",
     *     type="integer",
     *     example="2",
     *     nullable="true"
     * ),
     * @OA\Property(
     *     property="job_id",
     *     type="integer",
     *     example="3",
     * ),
     * @OA\Property(
     *     property="answer",
     *     type="string",
     *     example="Maybe",
     *     nullable="true"
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
        $jobQuestion = $this->resource->toArray();

        $result['site_survey_question_id'] = $jobQuestion['id'];
        if (!empty($jobQuestion['pivot'])) {
            $result['site_survey_question_option_id'] = $jobQuestion['pivot']['site_survey_question_option_id'];
            $result['job_id']                         = $jobQuestion['pivot']['job_id'];
            $result['answer']                         = $jobQuestion['pivot']['answer'];
        }

        return $result;
    }
}
