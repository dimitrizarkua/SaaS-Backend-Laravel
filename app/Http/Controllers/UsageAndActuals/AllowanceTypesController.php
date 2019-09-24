<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Models\AllowanceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateAllowanceTypeRequest;
use App\Http\Requests\UsageAndActuals\UpdateAllowanceTypeRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\AllowanceTypeListResponse;
use App\Http\Responses\UsageAndActuals\AllowanceTypeResponse;

/**
 * Class AllowanceTypesController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class AllowanceTypesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/usage-and-actuals/allowance-types",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Get list of allowance types",
     *      description="Returns list of allowance types. **`allowances.view`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AllowanceTypeListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('allowances.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = AllowanceType::paginate(Paginator::resolvePerPage());

        return AllowanceTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/usage-and-actuals/allowance-types",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Create new allowance type.",
     *      description="Create new allowance type. **`management.jobs.allowances`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateAllowanceTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AllowanceTypeResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\UsageAndActuals\CreateAllowanceTypeRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateAllowanceTypeRequest $request)
    {
        $this->authorize('management.jobs.allowances');

        $allowanceType = AllowanceType::create($request->validated());
        $allowanceType->saveOrFail();

        return AllowanceTypeResponse::make($allowanceType, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/allowance-types/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Returns full information about allowance type.",
     *      description="Returns full information about allowance type. **`allowances.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AllowanceTypeResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\AllowanceType $allowanceType
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(AllowanceType $allowanceType)
    {
        $this->authorize('allowances.view');

        return AllowanceTypeResponse::make($allowanceType);
    }

    /**
     * @OA\Patch(
     *      path="/usage-and-actuals/allowance-types/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Update existing allowance type.",
     *      description="Update existing allowance type. **`management.jobs.allowances`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateAllowanceTypeRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AllowanceTypeResponse")
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
     * @param \App\Http\Requests\UsageAndActuals\UpdateAllowanceTypeRequest $request
     * @param \App\Components\UsageAndActuals\Models\AllowanceType          $allowanceType
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateAllowanceTypeRequest $request, AllowanceType $allowanceType)
    {
        $this->authorize('management.jobs.allowances');

        $allowanceType->fillFromRequest($request);

        return AllowanceTypeResponse::make($allowanceType);
    }

    /**
     * @OA\Delete(
     *      path="/usage-and-actuals/allowance-types/{id}",
     *      tags={"Usage and Actuals", "Labours"},
     *      summary="Delete existing allowance type.",
     *      description="Delete existing allowance type. **`management.jobs.allowances`**
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
     * @param \App\Components\UsageAndActuals\Models\AllowanceType $allowanceType
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(AllowanceType $allowanceType)
    {
        $this->authorize('management.jobs.allowances');

        $allowanceType->delete();

        return ApiOKResponse::make();
    }
}
