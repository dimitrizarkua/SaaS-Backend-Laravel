<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetFaceFibre;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateCarpetFaceFibreRequest;
use App\Http\Requests\AssessmentReports\UpdateCarpetFaceFibreRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\CarpetFaceFibreListResponse;
use App\Http\Responses\AssessmentReports\CarpetFaceFibreResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetFaceFibresController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class CarpetFaceFibresController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-face-fibres",
     *     tags={"Assessment Reports"},
     *     summary="Get all carpet face fibres",
     *     description="Get all carpet face fibres. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetFaceFibreListResponse")
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
        $pagination = CarpetFaceFibre::paginate(Paginator::resolvePerPage());

        return CarpetFaceFibreListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/carpet-face-fibres",
     *     tags={"Assessment Reports"},
     *     summary="Create new carpet face fibre",
     *     description="Create new carpet face fibre. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateCarpetFaceFibreRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetFaceFibreResponse")
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
     * @param CreateCarpetFaceFibreRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateCarpetFaceFibreRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = CarpetFaceFibre::create($request->validated());
        $model->saveOrFail();

        return CarpetFaceFibreResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-face-fibres/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific carpet face fibre",
     *     description="Get full information about specific carpet face fibre. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetFaceFibreResponse")
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
     * @param CarpetFaceFibre $carpetFaceFibre
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CarpetFaceFibre $carpetFaceFibre)
    {
        $this->authorize('management.jobs.settings');

        return CarpetFaceFibreResponse::make($carpetFaceFibre);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/carpet-face-fibres/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing carpet face fibre",
     *     description="Update existing carpet face fibre. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateCarpetFaceFibreRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetFaceFibreResponse")
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
     * @param UpdateCarpetFaceFibreRequest $request
     * @param CarpetFaceFibre              $carpetFaceFibre
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateCarpetFaceFibreRequest $request, CarpetFaceFibre $carpetFaceFibre)
    {
        $this->authorize('management.jobs.settings');
        $carpetFaceFibre->fillFromRequest($request);

        return CarpetFaceFibreResponse::make($carpetFaceFibre);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/carpet-face-fibres/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing carpet face fibre",
     *     description=" Delete existing carpet face fibre. **`management.jobs.settings`** permission is required
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
     * @param int $carpetFaceFibreId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $carpetFaceFibreId)
    {
        $this->authorize('management.jobs.settings');

        CarpetFaceFibre::destroy($carpetFaceFibreId);

        return ApiOKResponse::make();
    }
}
