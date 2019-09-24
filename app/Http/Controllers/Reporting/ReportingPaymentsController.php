<?php

namespace App\Http\Controllers\Reporting;

use App\Components\Pagination\Paginator;
use App\Components\Reporting\Interfaces\ReportingPaymentsServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\FilterInvoicePaymentsReportRequest;
use App\Http\Responses\Reporting\InvoicePaymentsReportResponse;

/**
 * Class ReportingPaymentsController
 *
 * @package App\Http\Controllers\Finance
 */
class ReportingPaymentsController extends Controller
{
    /**
     * @var ReportingPaymentsServiceInterface
     */
    private $service;

    /**
     * ReportingPaymentsController constructor.
     *
     * @param ReportingPaymentsServiceInterface $reportingService
     */
    public function __construct(ReportingPaymentsServiceInterface $reportingService)
    {
        $this->service = $reportingService;
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/invoices/payments",
     *      tags={"Finance", "Invoices", "Reporting"},
     *      summary="Return invoices and associated payments.",
     *      description="Return report to show a detailed list all invoices and any payments made.
    Pagination is enabled for this endpoint. **`finance.invoices.reports.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter invoices by location_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="query",
     *         description="Allows to filter invoices by recipient_contact_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Allows to filter invoices by date. Invoices with date greater or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Allows to filter by invoices date. Invoices with date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Allows to filter invoices by type.",
     *         @OA\Schema(
     *              type="string",
     *              enum={"credit_card","direct_deposit","credit_note"},
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="amount_from",
     *         in="query",
     *         description="Allows to filter invoices by amount. Invoices with amount greater or equal to the
    given amount would be selected.",
     *         @OA\Schema(
     *              type="float",
     *              example="1000.50",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="amount_to",
     *         in="query",
     *         description="Allows to filter invoices by amount. Invoices with amount less or equal to the
    given amount would be selected.",
     *         @OA\Schema(
     *              type="float",
     *              example="1000.50",
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicePaymentsReportResponse")
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
     * @param FilterInvoicePaymentsReportRequest $request
     *
     * @return \App\Http\Responses\Reporting\InvoicePaymentsReportResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function invoicePaymentsReport(FilterInvoicePaymentsReportRequest $request)
    {
        $this->authorize('finance.invoices.reports.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $this->service->getInvoicePaymentsReportBuilder($request->getInvoicePaymentsReportFilter())
            ->paginate(Paginator::resolvePerPage());

        return new InvoicePaymentsReportResponse($pagination->getItems(), $pagination->getPaginationData());
    }
}
