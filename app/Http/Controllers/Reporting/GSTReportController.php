<?php

namespace App\Http\Controllers\Reporting;

use App\Components\Reporting\Services\GSTReportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\GSTReportRequest;
use App\Http\Responses\Reporting\GSTReportResponse;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Class GSTReportController
 *
 * @package App\Http\Controllers\Reporting
 */
class GSTReportController extends Controller
{
    /**
     * @var GSTReportService
     */
    private $reportService;

    /**
     * GSTReportController constructor.
     *
     * @param GSTReportService $reportService
     */
    public function __construct(GSTReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/gst",
     *      tags={"Finance", "Reporting"},
     *      summary="Return GST report.",
     *      description="Return GST report. **`finance.invoices.reports.view`** permission is required to perform
    this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter by location_id.",
     *         required=true,
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Allows to filter report records by date.",
     *         required=true,
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         required=true,
     *         description="Allows to filter report records by date.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GSTReportResponse")
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
     *
     * @param GSTReportRequest $request
     *
     * @throws AuthorizationException
     * @throws \JsonMapper_Exception
     * @return GSTReportResponse
     */
    public function index(GSTReportRequest $request): GSTReportResponse
    {
        $this->authorize('finance.invoices.reports.view');
        $report = $this->reportService->getReport($request->getFilter());

        return GSTReportResponse::make($report);
    }
}
