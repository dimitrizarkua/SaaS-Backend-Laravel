<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateCarpetTypeRequest;
use App\Http\Requests\AssessmentReports\UpdateCarpetTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\CarpetTypeListResponse;
use App\Http\Responses\AssessmentReports\CarpetTypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetTypesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class CarpetTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-types",
     *     tags={"Assessment Reports"},
     *     summary="Get all carpet types",
     *     description="Get all carpet types. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetTypeListResponse")
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
        $pagination = CarpetType::paginate(Paginator::resolvePerPage());

        return CarpetTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/carpet-types",
     *     tags={"Assessment Reports"},
     *     summary="Create new carpet type",
     *     description="Create new carpet type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateCarpetTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetTypeResponse")
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
     * @param CreateCarpetTypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateCarpetTypeRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = CarpetType::create($request->validated());
        $model->saveOrFail();

        return CarpetTypeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific carpet type",
     *     description="Get full information about specific carpet type. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetTypeResponse")
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
     * @param CarpetType $CarpetType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CarpetType $CarpetType)
    {
        $this->authorize('jobs.view');

        return CarpetTypeResponse::make($CarpetType);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/carpet-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing carpet type",
     *     description="Update existing carpet type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateCarpetTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetTypeResponse")
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
     * @param UpdateCarpetTypeRequest $request
     * @param CarpetType              $CarpetType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateCarpetTypeRequest $request, CarpetType $CarpetType)
    {
        $this->authorize('management.jobs.settings');
        $CarpetType->fillFromRequest($request);

        return CarpetTypeResponse::make($CarpetType);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/carpet-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing carpet types",
     *     description=" Delete existing carpet types. **`management.jobs.settings`** permission
    is required to perform this operation.",
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
     * @param int $carpetTypeId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $carpetTypeId)
    {
        $this->authorize('management.jobs.settings');

        CarpetType::destroy($carpetTypeId);

        return ApiOKResponse::make();
    }
}
