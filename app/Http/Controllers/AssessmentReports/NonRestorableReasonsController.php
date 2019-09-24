<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\NonRestorableReason;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateNonRestorableReasonRequest;
use App\Http\Requests\AssessmentReports\UpdateNonRestorableReasonRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\NonRestorableReasonListResponse;
use App\Http\Responses\AssessmentReports\NonRestorableReasonResponse;
use OpenApi\Annotations as OA;

/**
 * Class NonRestorableReasonsController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class NonRestorableReasonsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/non-restorable-reasons",
     *     tags={"Assessment Reports"},
     *     summary="Get all non restorable reasons",
     *     description="Get all non restorable reasons. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NonRestorableReasonListResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     * )
     * @return \App\Http\Responses\ApiOKResponse;
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('jobs.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = NonRestorableReason::paginate(Paginator::resolvePerPage());

        return NonRestorableReasonListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/non-restorable-reasons",
     *     tags={"Assessment Reports"},
     *     summary="Create new non restorable reason",
     *     description="Create new non restorable reason. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateNonRestorableReasonRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NonRestorableReasonResponse")
     *      ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param CreateNonRestorableReasonRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateNonRestorableReasonRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = NonRestorableReason::create($request->validated());
        $model->saveOrFail();

        return NonRestorableReasonResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/non-restorable-reasons/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific non restorable reason",
     *     description="Get full information about specific non restorable reason. **`jobs.view`**
    permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NonRestorableReasonResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource could not be found.",
     *     ),
     * )
     * @param NonRestorableReason $nonRestorableReason
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(NonRestorableReason $nonRestorableReason)
    {
        $this->authorize('jobs.view');

        return NonRestorableReasonResponse::make($nonRestorableReason);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/non-restorable-reasons/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing non restorable reason",
     *     description="Update existing non restorable reason. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateNonRestorableReasonRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/NonRestorableReasonResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource could not be found.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param UpdateNonRestorableReasonRequest $request
     * @param NonRestorableReason              $nonRestorableReason
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateNonRestorableReasonRequest $request, NonRestorableReason $nonRestorableReason)
    {
        $this->authorize('management.jobs.settings');
        $nonRestorableReason->fillFromRequest($request);

        return NonRestorableReasonResponse::make($nonRestorableReason);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/non-restorable-reasons/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing non restorable reasons",
     *     description=" Delete existing non restorable reasons. **`management.jobs.settings`** permission is required
    to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Requested resource could not be found.",
     *     ),
     * )
     * @param int $nonRestorableReasonId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $nonRestorableReasonId)
    {
        $this->authorize('management.jobs.settings');

        NonRestorableReason::destroy($nonRestorableReasonId);

        return ApiOKResponse::make();
    }
}
