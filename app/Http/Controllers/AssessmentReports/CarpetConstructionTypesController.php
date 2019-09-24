<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\CarpetConstructionType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateCarpetConstructionTypeRequest;
use App\Http\Requests\AssessmentReports\UpdateCarpetConstructionTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\CarpetConstructionTypeListResponse;
use App\Http\Responses\AssessmentReports\CarpetConstructionTypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class CarpetConstructionTypesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class CarpetConstructionTypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-construction-types",
     *     tags={"Assessment Reports"},
     *     summary="Get all carpet construction types",
     *     description="Get all carpet construction types. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetConstructionTypeListResponse")
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
        $pagination = CarpetConstructionType::paginate(Paginator::resolvePerPage());

        return CarpetConstructionTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/carpet-construction-types",
     *     tags={"Assessment Reports"},
     *     summary="Create new carpet construction type",
     *     description="Create new carpet construction type. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateCarpetConstructionTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetConstructionTypeResponse")
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
     * @param CreateCarpetConstructionTypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateCarpetConstructionTypeRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = CarpetConstructionType::create($request->validated());
        $model->saveOrFail();

        return CarpetConstructionTypeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/carpet-construction-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific carpet construction type",
     *     description="Get full information about specific carpet construction type. **`jobs.view`**
    permission is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetConstructionTypeResponse")
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
     * @param CarpetConstructionType $carpetConstructionType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(CarpetConstructionType $carpetConstructionType)
    {
        $this->authorize('jobs.view');

        return CarpetConstructionTypeResponse::make($carpetConstructionType);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/carpet-construction-types/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing carpetconstruction type",
     *     description="Update existing carpet construction type. **`management.jobs.settings`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateCarpetConstructionTypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CarpetConstructionTypeResponse")
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
     * @param UpdateCarpetConstructionTypeRequest $request
     * @param CarpetConstructionType              $carpetConstructionType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateCarpetConstructionTypeRequest $request, CarpetConstructionType $carpetConstructionType)
    {
        $this->authorize('management.jobs.settings');
        $carpetConstructionType->fillFromRequest($request);

        return CarpetConstructionTypeResponse::make($carpetConstructionType);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/carpet-construction-type/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing carpet construction type",
     *     description=" Delete existing carpet construction type. **`management.jobs.settings`** permission
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
     * @param int $carpetConstructionType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $carpetConstructionType)
    {
        $this->authorize('management.jobs.settings');

        CarpetConstructionType::destroy($carpetConstructionType);

        return ApiOKResponse::make();
    }
}
