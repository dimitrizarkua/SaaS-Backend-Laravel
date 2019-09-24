<?php

namespace App\Http\Controllers\Reporting;

use App\Components\Reporting\Services\FinancialAccountsReceivablesReportService;
use App\Components\Reporting\Services\FinancialRevenueReportService;
use App\Components\Reporting\Services\FinancialVolumeReportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\FilterFinancialReportRequest;
use App\Http\Responses\Reporting\FinancialAccountsReceivablesReportResponse;
use App\Http\Responses\Reporting\FinancialRevenueReportResponse;
use App\Http\Responses\Reporting\FinancialVolumeReportResponse;

/**
 * Class ReportingFinancialController
 *
 * @package App\Http\Controllers\Finance
 */
class ReportingFinancialController extends Controller
{
    /**
     * @var FinancialVolumeReportService
     */
    private $financialVolumeReportService;
    /**
     * @var FinancialRevenueReportService
     */
    private $financialRevenueReportService;
    /**
     * @var FinancialAccountsReceivablesReportService
     */
    private $financialAccountsReceivablesReportService;

    /**
     * ReportingPaymentsController constructor.
     *
     * @param FinancialVolumeReportService              $financialVolumeReportServiceService
     * @param FinancialRevenueReportService             $financialRevenueReportService
     * @param FinancialAccountsReceivablesReportService $financialAccountsReceivablesReportService
     */
    public function __construct(
        FinancialVolumeReportService $financialVolumeReportServiceService,
        FinancialRevenueReportService $financialRevenueReportService,
        FinancialAccountsReceivablesReportService $financialAccountsReceivablesReportService
    ) {
        $this->financialVolumeReportService              = $financialVolumeReportServiceService;
        $this->financialRevenueReportService             = $financialRevenueReportService;
        $this->financialAccountsReceivablesReportService = $financialAccountsReceivablesReportService;
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/financial/volume",
     *      tags={"Finance", "Reporting"},
     *      summary="Return financial volume.",
     *      description="Return report to show finacial volume and data for chart. **`finance.financial.reports.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter by location_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="gl_account_id",
     *         in="query",
     *         description="Allows to filter by specified gl account identifier.",
     *         @OA\Schema(
     *          description="GL account identifier.",
     *          type="integer",
     *          example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_ids[]",
     *         in="query",
     *         description="Allows to filter by tags ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="current_date_from",
     *         in="query",
     *         description="Allows to filter by date. Entities with date greater or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="current_date_to",
     *         in="query",
     *         description="Allows to filter by date. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="previous_date_from",
     *         in="query",
     *         description="Allows to filter by date for comparing. Entities with date greater or equal to the given
    date would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="previous_date_to",
     *         in="query",
     *         description="Allows to filter by date for comparing. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FinancialVolumeReportResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Reporting\FilterFinancialReportRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function volumeReport(FilterFinancialReportRequest $request)
    {
        $this->authorize('finance.financial.reports.view');

        $data = $this->financialVolumeReportService->getReport($request->getFinancialReportFilter());

        return FinancialVolumeReportResponse::make($data);
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/financial/revenue",
     *      tags={"Finance", "Reporting"},
     *      summary="Return financial revenue.",
     *      description="Return report to show finacial revenue and data for chart.
     *      **`finance.financial.reports.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter by location_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="gl_account_id",
     *         in="query",
     *         description="Allows to filter by specified gl account identifier.",
     *         @OA\Schema(
     *          type="integer",
     *          example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_ids[]",
     *         in="query",
     *         description="Allows to filter by tags ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="current_date_from",
     *         in="query",
     *         description="Allows to filter by date. Entities with date greater or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="current_date_to",
     *         in="query",
     *         description="Allows to filter by date. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="previous_date_from",
     *         in="query",
     *         description="Allows to filter by date for comparing. Entities with date greater or equal to the given
    date would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="previous_date_to",
     *         in="query",
     *         description="Allows to filter by date for comparing. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FinancialRevenueReportResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Reporting\FilterFinancialReportRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function revenueReport(FilterFinancialReportRequest $request)
    {
        $this->authorize('finance.financial.reports.view');

        $data = $this->financialRevenueReportService->getReport($request->getFinancialReportFilter());

        return FinancialRevenueReportResponse::make($data);
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/financial/accounts_receivables",
     *      tags={"Finance", "Reporting"},
     *      summary="Return financial revenue.",
     *      description="Return report to show finacial revenue and data for chart.
     *      **`finance.financial.reports.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter by location_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="gl_account_id",
     *         in="query",
     *         description="Allows to filter by specified gl account identifier.",
     *         @OA\Schema(
     *          type="integer",
     *          example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_ids[]",
     *         in="query",
     *         description="Allows to filter by tags ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="current_date_from",
     *         in="query",
     *         description="Allows to filter by date. Entities with date greater or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="current_date_to",
     *         in="query",
     *         description="Allows to filter by date. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="previous_date_from",
     *         in="query",
     *         description="Allows to filter by date for comparing. Entities with date greater or equal to the given
    date would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="previous_date_to",
     *         in="query",
     *         description="Allows to filter by date for comparing. Entities with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FinancialAccountsReceivablesReportResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Reporting\FilterFinancialReportRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function accountsReceivablesReport(FilterFinancialReportRequest $request)
    {
        $this->authorize('finance.financial.reports.view');

        $data = $this->financialAccountsReceivablesReportService->getReport($request->getFinancialReportFilter());

        return FinancialAccountsReceivablesReportResponse::make($data);
    }
}
