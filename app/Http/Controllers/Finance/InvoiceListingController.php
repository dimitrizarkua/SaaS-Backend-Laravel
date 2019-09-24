<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface;
use App\Components\Finance\Interfaces\InvoiceListingServiceInterface;
use App\Components\Finance\Models\Invoice;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\GetInfoRequest;
use App\Http\Requests\Finance\GetInvoiceListingsRequest;
use App\Http\Requests\Finance\SearchInvoicesByIdOrJobRequest;
use App\Http\Requests\Finance\SearchInvoicesRequest;
use App\Http\Requests\Finance\UnforwardedPaymentsRequest;
use App\Http\Responses\Finance\InvoicesInfoResponse;
use App\Http\Responses\Finance\InvoicesListResponse;
use App\Http\Responses\Finance\InvoicesSearchResponse;
use OpenApi\Annotations as OA;

/**
 * Class InvoiceListingController
 *
 * @package App\Http\Controllers\Finance
 */
class InvoiceListingController extends Controller
{
    /**
     * @var InvoiceListingServiceInterface
     */
    private $listingService;

    /**
     * InvoiceListingController constructor.
     *
     * @param InvoiceListingServiceInterface $listingService
     */
    public function __construct(InvoiceListingServiceInterface $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/all",
     *      tags={"Finance", "Invoices"},
     *      summary="Return all invoices.",
     *      description="Return all invoices. Pagination is enabled for this endpoint.  **`finance.invoices.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter invoices by location ids. If empty then locations belongs to authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="query",
     *         description="Allows to filter invoices by recipient_contatct_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="query",
     *         description="Allows to filter invoices by job_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_from",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date greater or equal to the given
     *         date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_to",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date less or equal to the given date
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
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesListResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param GetInvoiceListingsRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function index(GetInvoiceListingsRequest $request)
    {
        $this->authorize('finance.invoices.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $this->listingService->getAllInvoicesList($request->getInvoiceListingFilter())
            ->paginate(Paginator::resolvePerPage());

        return new InvoicesListResponse($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/search",
     *      tags={"Finance", "Invoices"},
     *      summary="Search for invoices",
     *      description="Search for invoices.  **`finance.invoices.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=true,
     *         description="Allows to search invoices by Id prefix",
     *         @OA\Schema(
     *            type="string",
     *            example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter search results by location ids. If empty then locations belongs to
     *         authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="virtual_status",
     *         in="query",
     *         description="Allows to filter search results by virtual status",
     *         @OA\Schema(
     *              ref="#/components/schemas/InvoiceVirtualStatuses"
     *         ),
     *      ),
     *      @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Allows to filter search results by real status",
     *         @OA\Schema(
     *              ref="#/components/schemas/FinancialEntityStatuses"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesSearchResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param SearchInvoicesRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchInvoicesRequest $request)
    {
        $this->authorize('finance.invoices.view');

        $response = Invoice::searchForNumbers(
            $request->getOptions(),
            $request->getLocationIds(),
            $request->getVirtualStatus(),
            $request->getStatus()
        );

        return new InvoicesSearchResponse($response);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/search-by-id-or-job",
     *      tags={"Finance", "Invoices"},
     *      summary="Search for invoices by list of ids or job ids",
     *      description="Search for invoices by list of ids or job ids.  **`finance.invoices.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="numbers[]",
     *         in="query",
     *         description="Allows to search invoices by list of ids or job ids.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesSearchResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Finance\SearchInvoicesByIdOrJobRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function searchByIdOrJobId(SearchInvoicesByIdOrJobRequest $request)
    {
        $this->authorize('finance.invoices.view');

        $response = Invoice::searchForNumbersOfEntitiesOrJobs($request->getNumbers());

        return new InvoicesSearchResponse($response);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/info",
     *      tags={"Finance","Invoices"},
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter counters by location ids. If empty then locations belongs to authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      summary="Returns invoices counters for different categories.",
     *      description="Returns invoices counters for different categories. **`finance.invoices.view`** permission
    is required to perform this operation.",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesInfoResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\GetInfoRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function info(GetInfoRequest $request)
    {
        $this->authorize('finance.invoices.view');
        $info = $this->listingService->getInvoiceCounters($request->getLocationIds());

        return InvoicesInfoResponse::make($info);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/draft",
     *      tags={"Finance","Invoices"},
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter invoices by location ids. If empty then locations belongs to authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="query",
     *         description="Allows to filter invoices by recipient_contatct_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="query",
     *         description="Allows to filter invoices by job_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_from",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date greater or equal to the given
     *         date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_to",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      summary="Return all draft invoices.",
     *      description="Return all draft invoices for locations to which currently authenticated user belongs. **
    `finance.invoices.view`** permission is required to perform this operation.",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesListResponse")
     *      ),
     * )
     * @param GetInvoiceListingsRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \JsonMapper_Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function draft(GetInvoiceListingsRequest $request)
    {
        $this->authorize('finance.invoices.view');
        $data = $this->listingService->getDraftInvoicesList($request->getInvoiceListingFilter());

        return InvoicesListResponse::make($data);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/unpaid",
     *      tags={"Finance","Invoices"},
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter invoices by location ids. If empty then locations belongs to authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="query",
     *         description="Allows to filter invoices by recipient_contatct_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="query",
     *         description="Allows to filter invoices by job_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_from",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date greater or equal to the given
     *         date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_to",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      summary="Return all approved invoices that are not fully paid.",
     *      description="Return all approved invoices that are not fully paid for locations to which currently
    authenticated user belongs. **`finance.invoices.view`** permission is required to perform this operation.",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesListResponse")
     *      ),
     * )
     * @param GetInvoiceListingsRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \JsonMapper_Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unpaid(GetInvoiceListingsRequest $request)
    {
        $this->authorize('finance.invoices.view');
        $data = $this->listingService->getUnpaidInvoicesList($request->getInvoiceListingFilter());

        return InvoicesListResponse::make($data);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/overdue",
     *      tags={"Finance","Invoices"},
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter invoices by location ids. If empty then locations belongs to authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="query",
     *         description="Allows to filter invoices by recipient_contatct_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="query",
     *         description="Allows to filter invoices by job_id.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_from",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date greater or equal to the given
     *         date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-01",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="due_date_to",
     *         in="query",
     *         description="Allows to filter by due_at date. Invoices with due_at date less or equal to the given date
    would be selected.",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10",
     *         )
     *      ),
     *      summary="Return all overdue invoices.",
     *      description="Return all overdue invoices for locations to which currently authenticated
    user belongs. **`finance.invoices.view`** permission is required to perform this operation.",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesListResponse")
     *      ),
     * )
     * @param GetInvoiceListingsRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \JsonMapper_Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function overdue(GetInvoiceListingsRequest $request)
    {
        $this->authorize('finance.invoices.view');
        $data = $this->listingService->getOverdueInvoicesList($request->getInvoiceListingFilter());

        return InvoicesListResponse::make($data);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/listings/unforwarded",
     *      tags={"Finance","Invoices"},
     *      security={{"passport": {}}},
     *      summary="Return all invoices which have unforwarded payments.",
     *      description="Return all invoices which have unforwarded payments for locations to which currently
     *      authenticated user belongs to. **`finance.invoices.view`** permission is required to perform this
     *      operation.",
     *      @OA\Parameter(
     *         name="location_id",
     *         in="query",
     *         description="Allows to filter invoices by location identifier.",
     *         @OA\Schema(
     *              type="integer",
     *              example=1
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesListResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized. One is only allowed to view unforwarded invoices.",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     * )
     * @param \App\Http\Requests\Finance\UnforwardedPaymentsRequest $request
     *
     * @return \App\Http\Responses\ApiResponse|InvoicesListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listUnforwarded(UnforwardedPaymentsRequest $request)
    {
        $this->authorize('finance.invoices.view');

        $forwardedService = app()->make(ForwardedPaymentsServiceInterface::class);

        $unforwardedIds = $forwardedService->getUnforwarded($request->getLocationId())
            ->pluck('invoice_id')
            ->toArray();

        $unforwardedInvoices = Invoice::query()
            ->whereIn('id', $unforwardedIds)
            ->get();

        return InvoicesListResponse::make($unforwardedInvoices);
    }
}
