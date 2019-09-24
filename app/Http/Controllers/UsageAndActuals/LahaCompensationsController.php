<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Models\LahaCompensation;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateLahaCompensationRequest;
use App\Http\Requests\UsageAndActuals\UpdateLahaCompensationRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\LahaCompensationListResponse;
use App\Http\Responses\UsageAndActuals\LahaCompensationResponse;

/**
 * Class LahaCompensationsController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class LahaCompensationsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/usage-and-actuals/laha-compensations",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Get list of laha compensations",
     *      description="Returns list of laha compensations. **`laha.view`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LahaCompensationListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('laha.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = LahaCompensation::paginate(Paginator::resolvePerPage());

        return LahaCompensationListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/usage-and-actuals/laha-compensations",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Create new laha compensation.",
     *      description="Create new laha compensation. **`management.jobs.laha`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateLahaCompensationRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LahaCompensationResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateLahaCompensationRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateLahaCompensationRequest $request)
    {
        $this->authorize('management.jobs.laha');

        $lahaCompensation = LahaCompensation::create($request->validated());
        $lahaCompensation->saveOrFail();

        return LahaCompensationResponse::make($lahaCompensation, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/laha-compensations/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Returns full information about laha compensation.",
     *      description="Returns full information about laha compenstion. **`laha.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LahaCompensationResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\LahaCompensation $lahaCompensation
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(LahaCompensation $lahaCompensation)
    {
        $this->authorize('laha.view');

        return LahaCompensationResponse::make($lahaCompensation);
    }

    /**
     * @OA\Patch(
     *      path="/usage-and-actuals/laha-compensations/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Update existing laha compensation.",
     *      description="Update existing laha compensation. **`management.jobs.laha`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateLahaCompensationRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LahaCompensationResponse")
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
     * @param \App\Http\Requests\UsageAndActuals\UpdateLahaCompensationRequest $request
     * @param \App\Components\UsageAndActuals\Models\LahaCompensation          $lahaCompensation
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateLahaCompensationRequest $request, LahaCompensation $lahaCompensation)
    {
        $this->authorize('management.jobs.laha');

        $lahaCompensation->fillFromRequest($request);

        return LahaCompensationResponse::make($lahaCompensation);
    }

    /**
     * @OA\Delete(
     *      path="/usage-and-actuals/laha-compensations/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Delete existing laha compensation.",
     *      description="Delete existing laha compensation. **`management.jobs.laha`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
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
     * @param \App\Components\UsageAndActuals\Models\LahaCompensation $lahaCompensation
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(LahaCompensation $lahaCompensation)
    {
        $this->authorize('management.jobs.laha');

        $lahaCompensation->delete();

        return ApiOKResponse::make();
    }
}
