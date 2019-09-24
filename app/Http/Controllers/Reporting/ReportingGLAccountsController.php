<?php

namespace App\Http\Controllers\Reporting;

use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Interfaces\TransactionsServiceInterface;
use App\Components\Finance\Models\GLAccount;
use App\Components\Pagination\Paginator;
use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\VO\GLAccountTransactionFilter;
use App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface;
use App\Exceptions\Api\ValidationException;
use App\Components\Reporting\Interfaces\IncomeReportServiceInterface;
use App\Components\Reporting\Models\Filters\IncomeReportFilter;
use App\Components\Reporting\Services\IncomeReportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reporting\IncomeReportRequest;
use App\Http\Requests\Finance\FilterGLAccountTransactionsRequest;
use App\Http\Requests\Finance\FilterGLAccountTrialReportRequest;
use App\Http\Responses\Reporting\IncomeReportResponse;
use App\Http\Responses\Reporting\GLAccountTransactionsReportResponse;
use App\Http\Responses\Reporting\GLAccountTrialReportResponse;
use InvalidArgumentException;
use OpenApi\Annotations as OA;

/**
 * Class ReportingGLAccountsController
 *
 * @package App\Http\Controllers\Reporting
 */
class ReportingGLAccountsController extends Controller
{
    /**
     * @var \App\Components\Finance\Interfaces\GLAccountServiceInterface
     */
    private $glAccountService;

    /**
     * @var \App\Components\Finance\Interfaces\TransactionsServiceInterface
     */
    private $transactionService;

    /**
     * @var \App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface
     */
    protected $accountingOrganizationsService;

    /**
     * @var \App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface
     */
    protected $reportingGLAccountService;

    /**
     * ReportingGLAccountsController constructor.
     *
     * @param \App\Components\Finance\Interfaces\GLAccountServiceInterface               $glAccountService
     * @param \App\Components\Finance\Interfaces\TransactionsServiceInterface            $transactionsService
     * @param \App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface $accountingOrganizationsService
     * @param \App\Components\Reporting\Interfaces\ReportingGLAccountServiceInterface    $reportingGLAccountService
     */
    public function __construct(
        GLAccountServiceInterface $glAccountService,
        TransactionsServiceInterface $transactionsService,
        AccountingOrganizationsServiceInterface $accountingOrganizationsService,
        ReportingGLAccountServiceInterface $reportingGLAccountService
    ) {
        $this->glAccountService               = $glAccountService;
        $this->transactionService             = $transactionsService;
        $this->accountingOrganizationsService = $accountingOrganizationsService;
        $this->reportingGLAccountService      = $reportingGLAccountService;
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/gl-accounts/transactions",
     *      tags={"Finance", "Reporting"},
     *      summary="Get filtered set of transactions by specified gl account identifier.",
     *      description="Allows to filter transactions by gl accounts. ``finance.gl_accounts.reports.view`` permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Allows to define start datetime range for filtering transactions.",
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2019-01-01"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Allows to define end datetime range for filtering transactions.",
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2019-02-02"
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="gl_account_id",
     *          in="query",
     *          required=true,
     *          description="Allows to define GL account.",
     *          @OA\Schema(
     *              type="integer",
     *              example="1"
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountTransactionsReportResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\FilterGLAccountTransactionsRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function listTransaction(FilterGLAccountTransactionsRequest $request)
    {
        $this->authorize('finance.gl_accounts.reports.view');

        $filter = new GLAccountTransactionFilter($request->validated());

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $this->glAccountService->findTransactionRecordsByAccount($request->getGlAccountId(), $filter)
            ->paginate(Paginator::resolvePerPage());

        return GLAccountTransactionsReportResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/gl-accounts/transactions/info",
     *      tags={"Finance", "Reporting"},
     *      summary="Get filtered set of transactions by specified gl account identifier for reporting with additional
    data and without pagination.",
     *      description="Allows to filter transactions by gl accounts. ``finance.gl_accounts.reports.view``
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="date_from",
     *          in="query",
     *          description="Allows to define start datetime range for filtering transactions.",
     *          @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="date_to",
     *          in="query",
     *          description="Allows to define end datetime range for filtering transactions.",
     *          @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-02-02"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="gl_account_id",
     *          in="query",
     *          required=true,
     *          description="Allows to define GL account.",
     *          @OA\Schema(
     *              type="integer",
     *              example="1"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/GLAccountTransactionsReportResponse")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\FilterGLAccountTransactionsRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function listTransactionReport(FilterGLAccountTransactionsRequest $request)
    {
        $this->authorize('finance.gl_accounts.reports.view');

        $glAccount = GLAccount::findOrFail($request->getGlAccountId());

        $filter                  = new GLAccountTransactionFilter($request->validated());
        $transactionRecordsQuery = $this->glAccountService->findTransactionRecordsByAccount($glAccount->id, $filter);
        $transactionRecords      = $transactionRecordsQuery->get();

        // Calculate start balance for account by transactions before date_from.
        $filterStartBalance = new GLAccountTransactionFilter([
            'date_to' => $filter->getDateFrom(),
        ]);
        $startBalance       = $this->glAccountService->getAccountBalance($glAccount->id, $filterStartBalance);

        $transactionRecordsWithBalance = $this->transactionService->addBalanceToTransactionRecords(
            $glAccount->accountType,
            $startBalance,
            $transactionRecords
        );

        $additional = [
            'gl_account'         => $glAccount,
            'total_transactions' => $transactionRecordsWithBalance->count(),
            'total_balance'      => $transactionRecords->isEmpty()
                ? (float)0
                : $transactionRecordsWithBalance->last()->balance,
        ];

        return GLAccountTransactionsReportResponse::make(
            $transactionRecordsWithBalance,
            null,
            200,
            [],
            $additional
        );
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/gl-accounts/income/summary",
     *      tags={"Finance", "Reporting"},
     *      summary="Get filtered set of gl accounts for reporting (Finance: Report - Income by Account Summary)
     *      with subtotals and total amount.",
     *      description="Allows to get filtered set of gl accounts for income summary report.
     *      ``finance.gl_accounts.reports.view`` permission is required to perform this operation.",
     *      security={{"passport": {}}},
     * @OA\Parameter(
     *         name="location_id",
     *         required=true,
     *         in="query",
     *         description="Allows to filter report data by specified location identifier.",
     *         @OA\Schema(
     *          description="Location identifier.",
     *          type="integer",
     *          example="1",
     *         )
     *      ),
     * @OA\Parameter(
     *         name="gl_account_id",
     *         in="query",
     *         description="Allows to filter report data by specified gl account identifier.",
     *         @OA\Schema(
     *          description="GL account identifier.",
     *          type="integer",
     *          example="1",
     *         )
     *      ),
     * @OA\Parameter(
     *         name="date_from",
     *         in="query",
     *         description="Allows to define start date range.",
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2019-01-01"
     *         )
     *      ),
     * @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Allows to define end date range.",
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2019-02-02"
     *         )
     *      ),
     * @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="query",
     *         description="Allows to define contact id.",
     *         @OA\Schema(
     *            type="integer",
     *            example="1"
     *         )
     *      ),
     * @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/IncomeReportResponse")
     *      ),
     * @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     * @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     * @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Reporting\IncomeReportRequest $request
     *
     * @return \App\Http\Responses\Reporting\IncomeReportResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function listIncomeReport(IncomeReportRequest $request): IncomeReportResponse
    {
        $this->authorize('finance.gl_accounts.reports.view');

        $filter = new IncomeReportFilter($request->validated());

        /** @var IncomeReportService $revenueReportService */
        $revenueReportService = app()->make(IncomeReportServiceInterface::class);

        $reportData = $revenueReportService->getIncomeReportData($filter);

        return new IncomeReportResponse($reportData);
    }

    /**
     * @OA\Get(
     *      path="/finance/reports/gl-accounts/trial-report",
     *      tags={"Finance", "Reporting"},
     *      summary="This report will show the trial balance up to the selected date.
     *      ``finance.gl_accounts.reports.view`` permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter by location.",
     *         required=true,
     *         @OA\Schema(
     *            description="Location identifier.",
     *            type="integer",
     *            example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="query",
     *         description="Allows to filter by end datetime.",
     *         required=true,
     *         @OA\Schema(
     *            type="string",
     *            format="date",
     *            example="2019-02-02"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountTrialReportResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\FilterGLAccountTrialReportRequest $request
     *
     * @return \App\Http\Responses\Reporting\GLAccountTrialReportResponse
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */

    public function trialReport(FilterGLAccountTrialReportRequest $request)
    {
        $this->authorize('finance.gl_accounts.reports.view');
        try {
            $data = $this->reportingGLAccountService->getGlAccountTrialReport(
                $request->getGLAccountTrialReportFilterData()
            );
        } catch (InvalidArgumentException $e) {
            throw new ValidationException($e->getMessage());
        }

        return new GLAccountTrialReportResponse($data);
    }
}
