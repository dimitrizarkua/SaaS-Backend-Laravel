<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Interfaces\JobSiteSurveyServiceInterface;
use App\Components\Jobs\Models\Job;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion as Question;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\AttachJobQuestionRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\JobSiteSurveyResponse;
use OpenApi\Annotations as OA;

/**
 * Class JobSiteSurveyController
 *
 * @package App\Http\Controllers\Jobs
 */
class JobSiteSurveyController extends Controller
{
    /**
     * @var JobSiteSurveyServiceInterface
     */
    protected $service;

    /**
     * JobSiteSurveyController constructor.
     *
     * @param JobSiteSurveyServiceInterface $service
     */
    public function __construct(JobSiteSurveyServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/jobs/{job_id}/site-survey",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Returns site survey for a job.",
     *     description="Returns site survey for a job. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/JobSiteSurveyResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Job doesn't exist.",
     *     ),
     * )
     * @param Job $job
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getSiteSurvey(Job $job)
    {
        $this->authorize('jobs.view');

        $siteSurvey = $this->service->getSiteSurvey($job->id);

        return JobSiteSurveyResponse::make($siteSurvey);
    }

    /**
     * @OA\Post(
     *     path="/jobs/{job_id}/site-survey/questions/{question_id}",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Attach a question option to a job.",
     *     description="Attach a question option to a job. **`jobs.update`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="question_id",
     *         in="path",
     *         required=true,
     *         description="Question option identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/AttachJobQuestionRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Job doesn't exist.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. Either job is closed or specified question was already answered for this job.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param AttachJobQuestionRequest $request
     * @param Job                      $job
     * @param Question                 $question
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function attachQuestion(AttachJobQuestionRequest $request, Job $job, Question $question)
    {
        $this->authorize('jobs.update');

        $this->service->attachQuestion(
            $job->id,
            $question->id,
            $request->getQuestionOptionId(),
            $request->getAnswer()
        );

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *     path="/jobs/{job_id}/site-survey/questions/{question_id}",
     *     tags={"Jobs", "Site Survey"},
     *     summary="Detach a question option from a job.",
     *     description="Detach a question option from a job. **`jobs.update`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         required=true,
     *         description="Job identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(
     *         name="question_id",
     *         in="path",
     *         required=true,
     *         description="Question option identifier",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Job doesn't exist.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Not allowed. No changes can be made to closed or cancelled job.",
     *     ),
     * )
     * @param Job      $job
     * @param Question $question
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function detachQuestion(Job $job, Question $question)
    {
        $this->authorize('jobs.update');

        $this->service->detachQuestion($job->id, $question->id);

        return ApiOKResponse::make();
    }
}
