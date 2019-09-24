<?php

namespace App\Http\Controllers\Management;

use App\Components\Pagination\Paginator;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion as Question;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption as QuestionOption;
use App\Exceptions\Api\NotAllowedException;
use App\Exceptions\Api\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteSurvey\CreateOrUpdateSiteSurveyQuestionOptionRequest as CreateOrUpdateQuestionOptionRequest;
use App\Http\Responses\SiteSurvey\SiteSurveyQuestionOptionListResponse as QuestionOptionListResponse;
use App\Http\Responses\SiteSurvey\SiteSurveyQuestionOptionResponse as QuestionOptionResponse;
use OpenApi\Annotations as OA;

/**
 * Class SiteSurveyQuestionOptionsController
 *
 * @package App\Http\Controllers\Management
 */
class SiteSurveyQuestionOptionsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/management/site-survey/questions/{question_id}/options",
     *      tags={"Management"},
     *      summary="Get all options of specific site survey question",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="question_id",
     *          in="path",
     *          required=true,
     *          description="Question identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionOptionListResponse")
     *      ),
     * )
     * @param Question $question
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Question $question)
    {
        $this->authorize('management.system.settings');
        /** @var Paginator $pagination */
        $pagination = QuestionOption::whereSiteSurveyQuestionId($question->id)
            ->paginate(Paginator::resolvePerPage());

        return QuestionOptionListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/management/site-survey/questions/{question_id}/options",
     *      tags={"Management"},
     *      summary="Create new site survey question option",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="question_id",
     *          in="path",
     *          required=true,
     *          description="Question identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateOrUpdateSiteSurveyQuestionOptionRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionOptionResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param CreateOrUpdateQuestionOptionRequest $request
     * @param Question                            $question
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \Throwable
     */
    public function store(CreateOrUpdateQuestionOptionRequest $request, Question $question)
    {
        $this->authorize('management.system.settings');

        /** @var QuestionOption $questionOption */
        $questionOption = $question->siteSurveyQuestionOptions()
            ->where('name', $request->getName())
            ->first();

        if ($questionOption) {
            throw new ValidationException(
                'Site survey question option with this name is already exists for specified question.'
            );
        } else {
            $questionOption = $question->siteSurveyQuestionOptions()
                ->create([
                    'name' => $request->getName(),
                ]);
        }

        return QuestionOptionResponse::make($questionOption, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/management/site-survey/questions/{question_id}/options/{question_option_id}",
     *      tags={"Management"},
     *      summary="Get full information about specific site survey question option",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="question_id",
     *          in="path",
     *          required=true,
     *          description="Question identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="question_option_id",
     *          in="path",
     *          required=true,
     *          description="Question option identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionOptionResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param Question $question
     * @param int      $questionOptionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\NotAllowedException
     */
    public function show(Question $question, int $questionOptionId)
    {
        $this->authorize('management.system.settings');

        /** @var QuestionOption $questionOption */
        $questionOption = $question->siteSurveyQuestionOptions()
            ->where('id', $questionOptionId)
            ->first();

        if (!$questionOption) {
            throw new NotAllowedException(
                'Site survey question option with this id is not found for specified question.'
            );
        }

        return QuestionOptionResponse::make($questionOption);
    }

    /**
     * @OA\Patch(
     *      path="/management/site-survey/questions/{question_id}/options/{question_option_id}",
     *      tags={"Management"},
     *      summary="Update existing site survey question option",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="question_id",
     *          in="path",
     *          required=true,
     *          description="Question identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="question_option_id",
     *          in="path",
     *          required=true,
     *          description="Question option identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateOrUpdateSiteSurveyQuestionOptionRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/SiteSurveyQuestionOptionResponse")
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
     * @param CreateOrUpdateQuestionOptionRequest $request
     * @param Question                            $question
     * @param int                                 $questionOptionId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\NotAllowedException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \Throwable
     */
    public function update(CreateOrUpdateQuestionOptionRequest $request, Question $question, int $questionOptionId)
    {
        $this->authorize('management.system.settings');

        /** @var QuestionOption $questionOption */
        $questionOption = $question->siteSurveyQuestionOptions()
            ->where('id', $questionOptionId)
            ->first();
        if (!$questionOption) {
            throw new NotAllowedException(
                'Site survey question option with this id is not found for specified question.'
            );
        }

        /** @var QuestionOption $questionOptionWithSameName */
        $questionOptionWithSameName = $question->siteSurveyQuestionOptions()
            ->where('name', $request->getName())
            ->first();
        if ($questionOptionWithSameName && $questionOption->id != $questionOptionWithSameName->id) {
            throw new ValidationException(
                'Site survey question option with this name is already exists for specified question.'
            );
        } else {
            $questionOption->fillFromRequest($request);
        }

        return QuestionOptionResponse::make($questionOption);
    }
}
