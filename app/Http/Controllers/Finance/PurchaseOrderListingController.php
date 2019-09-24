<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FilterPurchaseOrderListingsRequest;
use App\Http\Requests\Finance\GetInfoRequest;
use App\Http\Requests\Finance\SearchPurchaseOrderRequest;
use App\Http\Responses\Finance\PurchaseOrderInfoResponse;
use App\Http\Responses\Finance\PurchaseOrderListResponse;
use App\Http\Responses\Finance\PurchaseOrderSearchResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderListingController
 *
 * @package App\Http\Controllers\Finance
 */
class PurchaseOrderListingController extends Controller
{
    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface
     */
    private $service;

    /**
     * PurchaseOrderListingController constructor.
     *
     * @param \App\Components\Finance\Interfaces\PurchaseOrderListingServiceInterface $service
     */
    public function __construct(PurchaseOrderListingServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/listings/all",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Return all purchase orders.",
     *      description="Return all purchase orders. Pagination is enabled for this endpoint.**
    `finance.invoices.view`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter purchase orders list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter purchase orders by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter purchase orders by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderListResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param FilterPurchaseOrderListingsRequest $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     *
     * @return \App\Http\Responses\ApiOKResponse
     */
    public function index(FilterPurchaseOrderListingsRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $this->service->getAll($request->getPurchaseOrderListingFilter())
            ->paginate(Paginator::resolvePerPage());

        return new PurchaseOrderListResponse($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/listings/info",
     *      tags={"Finance", "Purchase Orders"},
     *      security={{"passport": {}}},
     *      summary="Returns purchase orders counters for different categories",
     *      description="Returns purchase orders counters for different categories: Draft, Pending Approval,
    Approved. **finance.purchase_orders.view** permission is required to perform this operation.",
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
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderInfoResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      )
     * )
     *
     * @return \App\Http\Responses\Finance\PurchaseOrderInfoResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\ValidationException
     */
    public function getInfo(GetInfoRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');
        $info = $this->service->getInfo($request->getLocationIds());

        return PurchaseOrderInfoResponse::make($info);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/listings/draft",
     *      tags={"Finance", "Purchase Orders"},
     *      security={{"passport": {}}},
     *      summary="Returns list of draft purchase orders",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter purchase orders list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter purchase orders by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter purchase orders by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderListResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\FilterPurchaseOrderListingsRequest $request
     *
     * @return \App\Http\Responses\Finance\PurchaseOrderListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \JsonMapper_Exception
     */
    public function getDraft(FilterPurchaseOrderListingsRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');
        $purchaseOrders = $this->service->getDraftPurchaseOrders($request->getPurchaseOrderListingFilter());

        return PurchaseOrderListResponse::make($purchaseOrders);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/listings/pending-approval",
     *      tags={"Finance", "Purchase Orders"},
     *      security={{"passport": {}}},
     *      summary="Returns list of pending approval purchase orders",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter purchase orders list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter purchase orders by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter purchase orders by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderListResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\FilterPurchaseOrderListingsRequest $request
     *
     * @return \App\Http\Responses\Finance\PurchaseOrderListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \JsonMapper_Exception
     */
    public function getPendingApproval(FilterPurchaseOrderListingsRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');
        $purchaseOrders = $this->service->getPendingApprovalPurchaseOrders($request->getPurchaseOrderListingFilter());

        return PurchaseOrderListResponse::make($purchaseOrders);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/listings/approved",
     *      tags={"Finance", "Purchase Orders"},
     *      security={{"passport": {}}},
     *      summary="Returns list of approved purchase orders",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter purchase orders list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter purchase orders by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter purchase orders by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter purchase orders by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderListResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\FilterPurchaseOrderListingsRequest $request
     *
     * @return \App\Http\Responses\Finance\PurchaseOrderListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \JsonMapper_Exception
     */
    public function getApproved(FilterPurchaseOrderListingsRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');
        $purchaseOrders = $this->service->getApprovedPurchaseOrders($request->getPurchaseOrderListingFilter());

        return PurchaseOrderListResponse::make($purchaseOrders);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/listings/search",
     *      summary="Allows to search purchase orders for numbers (id prefix)",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      tags={"Finance", "Purchase Orders", "Search"},
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          required=true,
     *          description="Number of purchase orders (id prefix)",
     *          @OA\Schema(
     *              type="string",
     *              example="10",
     *          )
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
     *              ref="#/components/schemas/PurchaseOrderVirtualStatuses"
     *         )
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
     *         description="Matching purchase orders",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderSearchResponse"),
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      )
     * )
     * @param SearchPurchaseOrderRequest $request
     *
     * @return \App\Http\Responses\Finance\PurchaseOrderSearchResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchPurchaseOrderRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');

        $response = PurchaseOrder::searchForNumbers(
            $request->getOptions(),
            $request->getLocationIds(),
            $request->getVirtualStatus(),
            $request->getStatus()
        );

        return new PurchaseOrderSearchResponse($response);
    }
}
