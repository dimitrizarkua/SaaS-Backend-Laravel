<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\PurchaseOrderItemsServiceInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreatePurchaseOrderItemRequest;
use App\Http\Requests\Finance\UpdatePurchaseOrderItemRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\FullPurchaseOrderItemResponse;
use App\Http\Responses\Finance\PurchaseOrderItemListResponse;
use App\Http\Responses\Finance\PurchaseOrderItemResponse;

/**
 * Class PurchaseOrderItemsController
 *
 * @package App\Http\Controllers\Finance
 */
class PurchaseOrderItemsController extends Controller
{
    /**
     * @var PurchaseOrderItemsServiceInterface
     */
    private $service;

    /**
     * PurchaseOrderItemsController constructor.
     *
     * @param PurchaseOrderItemsServiceInterface $service
     */
    public function __construct(PurchaseOrderItemsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{purchase_order_id}/items",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Get list of items of specific purchase order",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderItemListResponse"),
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      )
     *     )
     *
     * @param int $purchaseOrderId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(int $purchaseOrderId)
    {
        $this->authorize('finance.purchase_orders.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = PurchaseOrderItem::query()
            ->where('purchase_order_id', $purchaseOrderId)
            ->paginate(Paginator::resolvePerPage());

        return PurchaseOrderItemListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/finance/purchase-orders/{purchase_order_id}/items",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Create new purchase order item",
     *      description="**finance.purchase_orders.manage**, **finance.purchase_orders.manage_locked** (if purchase
    order is locked) permissions are required to perform this operation.",
     *     security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreatePurchaseOrderItemRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderItemResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param CreatePurchaseOrderItemRequest $request
     * @param PurchaseOrder                  $purchaseOrder
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     */
    public function store(CreatePurchaseOrderItemRequest $request, PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.manage');
        if ($purchaseOrder->isLocked()) {
            $this->authorize('finance.purchase_orders.manage_locked');
        }

        $purchaseOrderItem = $this->service->createPurchaseOrderItem($purchaseOrder->id, $request->validated());

        return PurchaseOrderItemResponse::make($purchaseOrderItem, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{purchase_order_id}/items/{purchase_order_item_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Returns full information about purchase order item",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="purchase_order_item_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order item identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullPurchaseOrderItemResponse")
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
     * @param int $purchaseOrderItemId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $purchaseOrderId, int $purchaseOrderItemId)
    {
        $this->authorize('finance.purchase_orders.view');
        $purchaseOrderItem = $this->service->getPurchaseOrderItem($purchaseOrderId, $purchaseOrderItemId);

        return FullPurchaseOrderItemResponse::make($purchaseOrderItem);
    }

    /**
     * @OA\Patch(
     *      path="/finance/purchase-orders/{purchase_order_id}/items/{purchase_order_item_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to update existing purchase order item",
     *      description="**finance.purchase_orders.manage**, **finance.purchase_orders.manage_locked** (if purchase
    order is locked) permissions are required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="purchase_order_item_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order item identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdatePurchaseOrderItemRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderItemResponse")
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
     * @param UpdatePurchaseOrderItemRequest $request
     * @param PurchaseOrder                  $purchaseOrder
     * @param int                            $purchaseOrderItemId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Exceptions\Api\ValidationException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     */
    public function update(
        UpdatePurchaseOrderItemRequest $request,
        PurchaseOrder $purchaseOrder,
        int $purchaseOrderItemId
    ) {
        $this->authorize('finance.purchase_orders.manage');
        if ($purchaseOrder->isLocked()) {
            $this->authorize('finance.purchase_orders.manage_locked');
        }

        $this->service->updatePurchaseOrderItem(
            $purchaseOrder->id,
            $purchaseOrderItemId,
            $request->validated()
        );

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/purchase-orders/{purchase_order_id}/items/{purchase_order_item_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Delete existing item of unapproved purchase order",
     *      description="**finance.purchase_orders.manage**, **finance.purchase_orders.manage_locked** (if purchase
    order is locked) permissions are required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="purchase_order_item_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order item identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
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
     * @param PurchaseOrder $purchaseOrder
     * @param int           $purchaseOrderItemId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \App\Components\Finance\Exceptions\NotAllowedException
     * @throws \Exception
     */
    public function destroy(PurchaseOrder $purchaseOrder, int $purchaseOrderItemId)
    {
        $this->authorize('finance.purchase_orders.manage');
        if ($purchaseOrder->isLocked()) {
            $this->authorize('finance.purchase_orders.manage_locked');
        }

        $this->service->deletePurchaseOrderItem($purchaseOrder->id, $purchaseOrderItemId);

        return ApiOKResponse::make();
    }
}
