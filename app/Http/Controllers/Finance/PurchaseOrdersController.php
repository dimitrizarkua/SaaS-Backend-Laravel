<?php

namespace App\Http\Controllers\Finance;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\VO\CreatePurchaseOrderData;
use App\Components\Finance\Services\PurchaseOrdersService;
use App\Exceptions\Api\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreatePurchaseOrderRequest;
use App\Http\Requests\Finance\UpdatePurchaseOrderRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\PurchaseOrderResponse;
use App\Http\Responses\Finance\PurchaseOrderSuggestedApproverListResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class PurchaseOrdersController
 *
 * @package App\Http\Controllers\Finance
 */
class PurchaseOrdersController extends Controller
{
    /**
     * @var PurchaseOrdersService
     */
    private $purchaseOrdersService;

    /**
     * @var DocumentsServiceInterface
     */
    private $documentsService;

    /**
     * PurchaseOrdersController constructor.
     *
     * @param PurchaseOrdersService $purchaseOrdersService
     * @param DocumentsServiceInterface      $documentsService
     */
    public function __construct(
        PurchaseOrdersService $purchaseOrdersService,
        DocumentsServiceInterface $documentsService
    ) {
        $this->purchaseOrdersService = $purchaseOrdersService;
        $this->documentsService      = $documentsService;
    }

    /**
     * @OA\Post(
     *      path="/finance/purchase-orders",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Create new purchase order",
     *      description="**finance.purchase_orders.manage** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreatePurchaseOrderRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderResponse")
     *       ),
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
     * @param \App\Http\Requests\Finance\CreatePurchaseOrderRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreatePurchaseOrderRequest $request)
    {
        $this->authorize('finance.purchase_orders.manage');
        $data = new CreatePurchaseOrderData($request->validated());
        $purchaseOrder = $this->purchaseOrdersService->create($data, Auth::id());

        return PurchaseOrderResponse::make($purchaseOrder, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Returns full information about purchase order",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $purchaseOrderId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $purchaseOrderId)
    {
        $this->authorize('finance.purchase_orders.view');

        $purchaseOrder = PurchaseOrder::with([
            'location',
            'accountingOrganization',
            'recipientContact',
            'job',
            'document',
            'items',
        ])->findOrFail($purchaseOrderId);

        return PurchaseOrderResponse::make($purchaseOrder);
    }

    /**
     * @OA\Patch(
     *      path="/finance/purchase-orders/{id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to update existing purchase order",
     *      description="**finance.purchase_orders.view**, **finance.purchase_orders.manage_locked** (if purchase order
    is locked) permissions are required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdatePurchaseOrderRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\UpdatePurchaseOrderRequest $request
     * @param \App\Components\Finance\Models\PurchaseOrder          $purchaseOrder
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \Throwable
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.manage');

        $forceUpdate = false;
        if (true === $purchaseOrder->isLocked()) {
            $this->authorize('finance.purchase_orders.manage_locked');
            $forceUpdate = true;
        }

        $updatedModel = $this->purchaseOrdersService->update($purchaseOrder->id, $request->validated(), $forceUpdate);

        return PurchaseOrderResponse::make($updatedModel);
    }

    /**
     * @OA\Delete(
     *      path="/finance/purchase-orders/{id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Delete existing unapproved purchase order",
     *      description="**finance.purchase_orders.manage**, **finance.purchase_orders.manage_locked** (if purchase
    order is locked) permissions are required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Could not delete either an approved purchase order
    or an order with approval requests.",
     *      ),
     * )
     *
     * @param \App\Components\Finance\Models\PurchaseOrder $purchaseOrder
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.manage');
        if ($purchaseOrder->isLocked()) {
            $this->authorize('finance.purchase_orders.manage_locked');
        }

        $this->purchaseOrdersService->delete($purchaseOrder->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/finance/purchase-orders/{id}/approve",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to approve existing purchase order",
     *      description="**finance.purchase_orders.manage** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
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
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Could not approve either an approved purchase order
    or the purchase order date is after the end-of-month financial date
    or your approve limit is less than the purchase order total amount.",
     *      ),
     * )
     *
     * @param int $purchaseOrderId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Throwable
     */
    public function approve(int $purchaseOrderId)
    {
        $this->authorize('finance.purchase_orders.manage');

        $this->purchaseOrdersService->approve($purchaseOrderId, Auth::user());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{id}/approver-list",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Returns list of users who can approve specified purchase order",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderSuggestedApproverListResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $purchaseOrderId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function getSuggestedApprovers(int $purchaseOrderId)
    {
        $this->authorize('finance.purchase_orders.view');

        $users = $this->purchaseOrdersService->getSuggestedApprovers($purchaseOrderId);

        return PurchaseOrderSuggestedApproverListResponse::make($users);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{id}/document",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to download PDF document generated for specific purchase order",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/octet-stream",
     *              @OA\Schema(type="file")
     *          )
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Finance\Models\PurchaseOrder $purchaseOrder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function document(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.view');
        if (null === $purchaseOrder->document_id) {
            throw new NotFoundException('A printed version of this purchase order doesn\'t exist.');
        }

        return $this->documentsService->getDocumentContentsAsResponse($purchaseOrder->document_id);
    }
}
