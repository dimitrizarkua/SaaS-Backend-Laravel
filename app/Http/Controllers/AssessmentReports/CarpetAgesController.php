<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetAge;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateCarpetAgeRequest;
use App\Http\Requests\AssessmentReports\UpdateCarpetAgeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\CarpetAgeListResponse;
use App\Http\Responses\AssessmentReports\CarpetAgeResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetAgesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class CarpetAgesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-ages",
     *     tags={"Assessment Reports"},
     *     summary="Get all carpet ages",
     *     description="Get all carpet ages. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetAgeListResponse")
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
        $pagination = CarpetAge::paginate(Paginator::resolvePerPage());

        return CarpetAgeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/carpet-ages",
     *     tags={"Assessment Reports"},
     *     summary="Create new carpet age",
     *     description="Create new carpet age. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateCarpetAgeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetAgeResponse")
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
     * @param CreateCarpetAgeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateCarpetAgeRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = CarpetAge::create($request->validated());
        $model->saveOrFail();

        return CarpetAgeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-ages/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific carpet age",
     *     description="Get full information about specific carpet age. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetAgeResponse")
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
     * @param CarpetAge $carpetAge
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CarpetAge $carpetAge)
    {
        $this->authorize('jobs.view');

        return CarpetAgeResponse::make($carpetAge);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/carpet-ages/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing carpet age",
     *     description="Update existing carpet age. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateCarpetAgeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetAgeResponse")
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
     * @param UpdateCarpetAgeRequest $request
     * @param CarpetAge              $carpetAge
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateCarpetAgeRequest $request, CarpetAge $carpetAge)
    {
        $this->authorize('management.jobs.settings');
        $carpetAge->fillFromRequest($request);

        return CarpetAgeResponse::make($carpetAge);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/carpet-ages/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing carpet age",
     *     description=" Delete existing carpet age. **`management.jobs.settings`** permission is required
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
     * @param int $carpetAgeId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $carpetAgeId)
    {
        $this->authorize('management.jobs.settings');

        CarpetAge::destroy($carpetAgeId);

        return ApiOKResponse::make();
    }
}
