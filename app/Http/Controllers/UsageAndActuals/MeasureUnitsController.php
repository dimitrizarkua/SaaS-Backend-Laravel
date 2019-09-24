<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Models\MeasureUnit;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateMeasureUnitRequest;
use App\Http\Requests\UsageAndActuals\UpdateMeasureUnitRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\MeasureUnitListResponse;
use App\Http\Responses\UsageAndActuals\MeasureUnitResponse;

/**
 * Class MeasureUnitsController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class MeasureUnitsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/usage-and-actuals/measure-units",
     *      tags={"Usage and Actuals"},
     *      summary="Get list of measure units.",
     *      description="Returns list of measure units. **`jobs.usage.view`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MeasureUnitListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('jobs.usage.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = MeasureUnit::paginate(Paginator::resolvePerPage());

        return MeasureUnitListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/usage-and-actuals/measure-units",
     *      tags={"Usage and Actuals"},
     *      summary="Create new measure unit.",
     *      description="Create new measure unit. **`management.materials.measure_units`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateMeasureUnitRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MeasureUnitResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateMeasureUnitRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateMeasureUnitRequest $request)
    {
        $this->authorize('management.materials.measure_units');

        $measureUnit = MeasureUnit::create($request->validated());
        $measureUnit->saveOrFail();

        return MeasureUnitResponse::make($measureUnit, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/measure-units/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Returns full information about measure unit.",
     *      description="Returns full information about measure unit. **`jobs.usage.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MeasureUnitResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\MeasureUnit $measureUnit
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(MeasureUnit $measureUnit)
    {
        $this->authorize('jobs.usage.view');

        return MeasureUnitResponse::make($measureUnit);
    }

    /**
     * @OA\Patch(
     *      path="/usage-and-actuals/measure-units/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Update existing measure unit.",
     *      description="Update existing measure unit. **`management.materials.measure_units`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateMeasureUnitRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/MeasureUnitResponse")
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
     * @param \App\Http\Requests\UsageAndActuals\UpdateMeasureUnitRequest $request
     * @param \App\Components\UsageAndActuals\Models\MeasureUnit          $measureUnit
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateMeasureUnitRequest $request, MeasureUnit $measureUnit)
    {
        $this->authorize('management.materials.measure_units');

        $measureUnit->fillFromRequest($request);

        return MeasureUnitResponse::make($measureUnit);
    }

    /**
     * @OA\Delete(
     *      path="/usage-and-actuals/measure-units/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Delete existing measure unit.",
     *      description="Delete existing measure unit. **`management.materials.measure_units`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\MeasureUnit $measureUnit
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(MeasureUnit $measureUnit)
    {
        $this->authorize('management.materials.measure_units');

        $measureUnit->delete();

        return ApiOKResponse::make();
    }
}
