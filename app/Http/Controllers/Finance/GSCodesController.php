<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\GSCode;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateGSCodeRequest;
use App\Http\Requests\Finance\UpdateGSCodeRequest;
use App\Http\Responses\Finance\GSCodeListResponse;
use App\Http\Responses\Finance\GSCodeResponse;

/**
 * Class GSCodesController
 *
 * @package App\Http\Controllers\Finance
 */
class GSCodesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/finance/gs-codes",
     *      tags={"Finance"},
     *      summary="Get list of GS Codes",
     *      description="Returns list of GS Codes. **`finance.gs_codes.view`** permission is required to perform this
     *      operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GSCodeListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('finance.gs_codes.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = GSCode::paginate(Paginator::resolvePerPage());

        return GSCodeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/finance/gs-codes",
     *      tags={"Finance"},
     *      summary="Create new GS Code",
     *      description="Create new GS Code. ``finance.gs_codes.manage`` permission is required to perform
     *      this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateGSCodeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GSCodeResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\CreateGSCodeRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateGSCodeRequest $request)
    {
        $this->authorize('finance.gs_codes.manage');
        $model = GSCode::create($request->validated());
        $model->save();

        return GSCodeResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/gs-codes/{id}",
     *      tags={"Finance"},
     *      summary="Returns full information about GS Code",
     *      description="Returns full information about GS Code. **`finance.gs_codes.manage`** permission is required
     *      to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GSCodeResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Finance\Models\GSCode $gsCode
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(GSCode $gsCode)
    {
        $this->authorize('finance.gs_codes.view');

        return GSCodeResponse::make($gsCode);
    }

    /**
     * @OA\Patch(
     *      path="/finance/gs-codes/{id}",
     *      tags={"Finance"},
     *      summary="Allows to update GS codes",
     *      description="Allows to update GS Codes. **`finance.gs_codes.manage`** permission
     * is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateGSCodeRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GSCodeResponse")
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
     * @param \App\Http\Requests\Finance\UpdateGSCodeRequest $request
     * @param \App\Components\Finance\Models\GSCode          $gsCode
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateGSCodeRequest $request, GSCode $gsCode)
    {
        $this->authorize('finance.gs_codes.manage');
        $gsCode->update($request->validated());

        return GSCodeResponse::make($gsCode);
    }
}
