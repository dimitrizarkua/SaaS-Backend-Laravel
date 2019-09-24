<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateFlooringTypeRequest;
use App\Http\Requests\AssessmentReports\UpdateFlooringTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\FlooringTypeListResponse;
use App\Http\Responses\AssessmentReports\FlooringTypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class FlooringTypesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class FlooringTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/flooring-types",
     *     tags={"Assessment Reports"},
     *     summary="Get all flooring types",
     *     description="Get all flooring types. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringTypeListResponse")
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
        $pagination = FlooringType::paginate(Paginator::resolvePerPage());

        return FlooringTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/flooring-types",
     *     tags={"Assessment Reports"},
     *     summary="Create new flooring type",
     *     description="Create new flooring type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateFlooringTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringTypeResponse")
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
     * @param CreateFlooringTypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateFlooringTypeRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = FlooringType::create($request->validated());
        $model->saveOrFail();

        return FlooringTypeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/flooring-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific flooring type",
     *     description="Get full information about specific flooring type. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringTypeResponse")
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
     * @param FlooringType $flooringType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(FlooringType $flooringType)
    {
        $this->authorize('jobs.view');

        return FlooringTypeResponse::make($flooringType);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/flooring-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing flooring type",
     *     description="Update existing flooring type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateFlooringTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringTypeResponse")
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
     * @param UpdateFlooringTypeRequest $request
     * @param FlooringType              $flooringType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateFlooringTypeRequest $request, FlooringType $flooringType)
    {
        $this->authorize('management.jobs.settings');
        $flooringType->fillFromRequest($request);

        return FlooringTypeResponse::make($flooringType);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/flooring-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing flooring type",
     *     description=" Delete existing flooring type. **`management.jobs.settings`** permission is required
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
     * @param int $flooringTypeId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $flooringTypeId)
    {
        $this->authorize('management.jobs.settings');

        FlooringType::destroy($flooringTypeId);

        return ApiOKResponse::make();
    }
}
