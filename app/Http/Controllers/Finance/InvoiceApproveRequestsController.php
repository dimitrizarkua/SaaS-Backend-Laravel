<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Services\InvoicesService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateApprovalRequestRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\ApproverListResponse;
use App\Http\Responses\Finance\InvoicesApproveRequestsListResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class InvoiceApproveRequestsController
 *
 * @package App\Http\Controllers\Finance
 */
class InvoiceApproveRequestsController extends Controller
{
    /**
     * @var InvoicesService
     */
    private $service;

    /**
     * InvoicesController constructor.
     *
     * @param InvoicesService $service
     */
    public function __construct(InvoicesService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices/{id}/approve-requests",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to create approve requests for specific invoice",
     *      description="Allows to create approve requests for specific invoice. **`finance.invoices.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateApprovalRequestRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param Invoice                      $invoice
     * @param CreateApprovalRequestRequest $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     *
     * @return ApiOKResponse
     */
    public function createApproveRequest(Invoice $invoice, CreateApprovalRequestRequest $request)
    {
        $this->authorize('finance.invoices.manage');
        $requesterId = $request->user()->id;

        $this->service->createApproveRequest(
            $invoice->id,
            $requesterId,
            $request->getApproverIdsList()
        );

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/{id}/approver-list",
     *      tags={"Finance", "Invoices"},
     *      summary="Returns list of users that are able to approve specific invoice",
     *      description="Returns list of users that are able to approve specific invoice. **`finance.invoices.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApproverListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function approverList(Invoice $invoice)
    {
        $this->authorize('finance.invoices.view');

        $list = User::query()
            ->whereHas('locations', function (Builder $query) use ($invoice) {
                return $query->where('id', $invoice->location_id);
            })
            ->where('invoice_approve_limit', '>=', $invoice->getTotalAmount())
            ->get();

        return ApproverListResponse::make($list);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/{id}/approve-requests",
     *      tags={"Finance", "Invoices"},
     *      summary="Returns list approve requests for specific invoice.",
     *      description="Returns list approve requests for specific invoice. **`finance.invoices.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesApproveRequestsListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function getApproveRequests(Invoice $invoice)
    {
        $this->authorize('finance.invoices.view');

        return InvoicesApproveRequestsListResponse::make($invoice->approveRequests);
    }
}
