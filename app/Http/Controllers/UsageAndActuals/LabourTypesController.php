<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Models\LabourType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateLabourTypeRequest;
use App\Http\Requests\UsageAndActuals\UpdateLabourTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\LabourTypeListResponse;
use App\Http\Responses\UsageAndActuals\LabourTypeResponse;

/**
 * Class LabourTypesController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class LabourTypesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/usage-and-actuals/labour-types",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Get list of labour types",
     *      description="Returns list of labour types. **`labour.view`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LabourTypeListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('labour.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = LabourType::paginate(Paginator::resolvePerPage());

        return LabourTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/usage-and-actuals/labour-types",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Create new labour type.",
     *      description="Create new labour type. **`management.jobs.labour`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateLabourTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LabourTypeResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateLabourTypeRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateLabourTypeRequest $request)
    {
        $this->authorize('management.jobs.labour');

        $labourType = LabourType::create($request->validated());
        $labourType->saveOrFail();

        return LabourTypeResponse::make($labourType, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/labour-types/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Returns full information about labour type.",
     *      description="Returns full information about labour type. **`labour.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LabourTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\LabourType $labourType
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(LabourType $labourType)
    {
        $this->authorize('labour.view');

        return LabourTypeResponse::make($labourType);
    }

    /**
     * @OA\Patch(
     *      path="/usage-and-actuals/labour-types/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Update existing labour type.",
     *      description="Update existing labour type. **`management.jobs.labour`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateLabourTypeRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LabourTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\UpdateLabourTypeRequest $request
     * @param \App\Components\UsageAndActuals\Models\LabourType          $labourType
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateLabourTypeRequest $request, LabourType $labourType)
    {
        $this->authorize('management.jobs.labour');

        $labourType->fillFromRequest($request);

        return LabourTypeResponse::make($labourType);
    }

    /**
     * @OA\Delete(
     *      path="/usage-and-actuals/labour-types/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Delete existing labour type.",
     *      description="Delete existing labour type. **`management.jobs.labour`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\LabourType $labourType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(LabourType $labourType)
    {
        $this->authorize('management.jobs.labour');

        $labourType->delete();

        return ApiOKResponse::make();
    }
}
