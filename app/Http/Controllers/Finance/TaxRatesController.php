<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\TaxRate;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateTaxRateRequest;
use App\Http\Requests\Finance\UpdateTaxRateRequest;
use App\Http\Responses\Finance\TaxRateListResponse;
use App\Http\Responses\Finance\TaxRateResponse;

/**
 * Class TaxRatesController
 *
 * @package App\Http\Controllers\Finance
 */
class TaxRatesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/finance/tax-rates",
     *      tags={"Finance"},
     *      summary="Get list of tax rates",
     *      description="Returns list of tax rates. **`finance.gl_accounts.manage`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('finance.gl_accounts.manage');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = TaxRate::paginate(Paginator::resolvePerPage());

        return TaxRateListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/finance/tax-rates",
     *      tags={"Finance"},
     *      summary="Create new tax rate",
     *      description="Create new tax rate. **`finance.gl_accounts.manage`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateTaxRateRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateTaxRateRequest $request)
    {
        $this->authorize('finance.gl_accounts.manage');
        $taxRate = TaxRate::create($request->validated());
        $taxRate->saveOrFail();

        return TaxRateResponse::make($taxRate, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/tax-rates/{id}",
     *      tags={"Finance"},
     *      summary="Returns full information about tax rate",
     *      description="Returns full information about tax rate. **`finance.gl_accounts.manage`** permission is
     *      required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(TaxRate $taxRate)
    {
        $this->authorize('finance.gl_accounts.manage');

        return TaxRateResponse::make($taxRate);
    }

    /**
     * @OA\Patch(
     *      path="/finance/tax-rates/{id}",
     *      tags={"Finance"},
     *      summary="Allows to update tax rate",
     *      description="Allows to update tax rate. **`finance.gl_accounts.manage`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateTaxRateRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TaxRateResponse")
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateTaxRateRequest $request, TaxRate $taxRate)
    {
        $this->authorize('finance.gl_accounts.manage');
        $taxRate->fillFromRequest($request);

        return TaxRateResponse::make($taxRate);
    }
}
