<?php

namespace App\Http\Controllers\AssessmentReports;

use App\Components\AssessmentReports\Models\FlooringSubtype;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssessmentReports\CreateFlooringSubtypeRequest;
use App\Http\Requests\AssessmentReports\UpdateFlooringSubtypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\AssessmentReports\FlooringSubtypeListResponse;
use App\Http\Responses\AssessmentReports\FlooringSubtypeResponse;
use OpenApi\Annotations as OA;

/**
 * Class FlooringSubtypesController
 *
 * @package App\Http\Controllers\AssessmentReports
 */
class FlooringSubtypesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/assessment-reports/flooring-subtypes",
     *     tags={"Assessment Reports"},
     *     summary="Get all flooring subtypes",
     *     description="Get all flooring subtypes. **`jobs.view`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringSubtypeListResponse")
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
        $pagination = FlooringSubtype::paginate(Paginator::resolvePerPage());

        return FlooringSubtypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *     path="/assessment-reports/flooring-subtypes",
     *     tags={"Assessment Reports"},
     *     summary="Create new flooring subtype",
     *     description="Create new flooring subtype. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/CreateFlooringSubtypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringSubtypeResponse")
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
     * @param CreateFlooringSubtypeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Throwable
     */
    public function store(CreateFlooringSubtypeRequest $request)
    {
        $this->authorize('management.jobs.settings');
        $model = FlooringSubtype::create($request->validated());
        $model->saveOrFail();

        return FlooringSubtypeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *     path="/assessment-reports/flooring-subtypes/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Get full information about specific flooring subtype",
     *     description="Get full information about specific flooring subtype. **`jobs.view`** permission
    is required to perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringSubtypeResponse")
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
     * @param FlooringSubtype $flooringSubtype
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(FlooringSubtype $flooringSubtype)
    {
        $this->authorize('jobs.view');

        return FlooringSubtypeResponse::make($flooringSubtype);
    }

    /**
     * @OA\Patch(
     *     path="/assessment-reports/flooring-subtypes/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Update existing flooring subtype",
     *     description="Update existing flooring subtype. **`management.jobs.settings`** permission is required to
    perform this operation.",
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/UpdateFlooringSubtypeRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FlooringSubtypeResponse")
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
     * @param UpdateFlooringSubtypeRequest $request
     * @param FlooringSubtype              $flooringSubtype
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateFlooringSubtypeRequest $request, FlooringSubtype $flooringSubtype)
    {
        $this->authorize('management.jobs.settings');
        $flooringSubtype->fillFromRequest($request);

        return FlooringSubtypeResponse::make($flooringSubtype);
    }

    /**
     * @OA\Delete(
     *     path="/assessment-reports/flooring-subtype/{id}",
     *     tags={"Assessment Reports"},
     *     summary="Delete existing flooring subtype",
     *     description=" Delete existing flooring subtype. **`management.jobs.settings`** permission is required
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
     * @param int $flooringSubtypeId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $flooringSubtypeId)
    {
        $this->authorize('management.jobs.settings');

        FlooringSubtype::destroy($flooringSubtypeId);

        return ApiOKResponse::make();
    }
}
