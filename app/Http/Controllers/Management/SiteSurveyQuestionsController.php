<?php

namespace App\Http\Controllers\Management;

use App\Components\Pagination\Paginator;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion as Question;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteSurvey\CreateSiteSurveyQuestionRequest as CreateQuestionRequest;
use App\Http\Requests\SiteSurvey\UpdateSiteSurveyQuestionRequest as UpdateQuestionRequest;
use App\Http\Responses\SiteSurvey\SiteSurveyQuestionListResponse as QuestionListResponse;
use App\Http\Responses\SiteSurvey\SiteSurveyQuestionResponse as QuestionResponse;
use OpenApi\Annotations as OA;

/**
 * Class SiteSurveyQuestionsController
 *
 * @package App\Http\Controllers\Management
 */
class SiteSurveyQuestionsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/management/site-survey/questions",
     *      tags={"Management"},
     *      summary="Get all site survey questions",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionListResponse")
     *      ),
     * )
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('management.system.settings');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = Question::paginate(Paginator::resolvePerPage());

        return QuestionListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/management/site-survey/questions",
     *      tags={"Management"},
     *      summary="Create new site survey question",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateSiteSurveyQuestionRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param CreateQuestionRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateQuestionRequest $request)
    {
        $this->authorize('management.system.settings');
        $question = Question::create($request->validated());
        $question->saveOrFail();

        return QuestionResponse::make($question, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/management/site-survey/questions/{id}",
     *      tags={"Management"},
     *      summary="Get full information about specific site survey question",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param Question $question
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Question $question)
    {
        $this->authorize('management.system.settings');

        return QuestionResponse::make($question);
    }

    /**
     * @OA\Patch(
     *      path="/management/site-survey/questions/{id}",
     *      tags={"Management"},
     *      summary="Update existing site survey question",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateSiteSurveyQuestionRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param UpdateQuestionRequest $request
     * @param Question              $question
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $this->authorize('management.system.settings');
        $question->fillFromRequest($request);

        return QuestionResponse::make($question);
    }
}
