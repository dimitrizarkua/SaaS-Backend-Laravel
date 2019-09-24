<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\UnderlayType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateUnderlayTypeRequest;
use App\Http\Requests\AssessmentReports\UpdateUnderlayTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\UnderlayTypeListResponse;
use App\Http\Responses\AssessmentReports\UnderlayTypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class UnderlayTypesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class UnderlayTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/underlay-types",
     *     tags={"Assessment Reports"},
     *     summary="Get all underlay types",
     *     description="Get all underlay types. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UnderlayTypeListResponse")
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
        $pagination = UnderlayType::paginate(Paginator::resolvePerPage());

        return UnderlayTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/underlay-types",
     *     tags={"Assessment Reports"},
     *     summary="Create new underlay type",
     *     description="Create new underlay type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateUnderlayTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UnderlayTypeResponse")
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
     * @param CreateUnderlayTypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateUnderlayTypeRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = UnderlayType::create($request->validated());
        $model->saveOrFail();

        return UnderlayTypeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/underlay-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific underlay type",
     *     description="Get full information about specific underlay type. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UnderlayTypeResponse")
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
     * @param UnderlayType $UnderlayType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(UnderlayType $UnderlayType)
    {
        $this->authorize('jobs.view');

        return UnderlayTypeResponse::make($UnderlayType);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/underlay-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing underlay type",
     *     description="Update existing underlay type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateUnderlayTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UnderlayTypeResponse")
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
     * @param UpdateUnderlayTypeRequest $request
     * @param UnderlayType              $UnderlayType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateUnderlayTypeRequest $request, UnderlayType $UnderlayType)
    {
        $this->authorize('management.jobs.settings');
        $UnderlayType->fillFromRequest($request);

        return UnderlayTypeResponse::make($UnderlayType);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/underlay-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing underlay type",
     *     description=" Delete existing underlay type. **`management.jobs.settings`** permission is required
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
     * @param int $underlayTypeId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $underlayTypeId)
    {
        $this->authorize('management.jobs.settings');

        UnderlayType::destroy($underlayTypeId);

        return ApiOKResponse::make();
    }
}
