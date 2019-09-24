<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Services\PurchaseOrdersService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreatePurchaseOrderApproveRequestsRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\PurchaseOrderApproveRequestListResponse;

/**
 * Class PurchaseOrderApproveRequestsController
 *
 * @package App\Http\Controllers\Finance
 */
class PurchaseOrderApproveRequestsController extends Controller
{
    /**
     * @var PurchaseOrdersService
     */
    private $service;

    /**
     * PurchaseOrderApproveRequestsController constructor.
     *
     * @param PurchaseOrdersService $service
     */
    public function __construct(PurchaseOrdersService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{id}/approve-requests",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Returns list of purchase order approve requests",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderApproveRequestListResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param PurchaseOrder $purchaseOrder
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getPurchaseOrderApproveRequests(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.view');

        return PurchaseOrderApproveRequestListResponse::make($purchaseOrder->approveRequests);
    }

    /**
     * @OA\Post(
     *      path="/finance/purchase-orders/{id}/approve-requests",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to create approve requests for a purchase order",
     *      description="**finance.purchase_orders.manage** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreatePurchaseOrderApproveRequestsRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApiOKResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param CreatePurchaseOrderApproveRequestsRequest $request
     * @param int                                       $purchaseOrderId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createPurchaseOrderApproveRequests(
        CreatePurchaseOrderApproveRequestsRequest $request,
        int $purchaseOrderId
    ) {
        $this->authorize('finance.purchase_orders.manage');

        $this->service->createApproveRequest(
            $purchaseOrderId,
            $request->user()->id,
            $request->getApprovers()
        );

        return ApiOKResponse::make();
    }
}
