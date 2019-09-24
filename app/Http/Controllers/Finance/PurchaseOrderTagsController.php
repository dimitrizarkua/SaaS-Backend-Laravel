<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Tags\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Tags\TagListResponse;
use OpenApi\Annotations as OA;

/**
 * Class PurchaseOrderTagsController
 *
 * @package App\Http\Controllers\Finance
 */
class PurchaseOrderTagsController extends Controller
{
    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface
     */
    protected $service;

    /**
     * PurchaseOrderTagsController constructor.
     *
     * @param \App\Components\Finance\Interfaces\PurchaseOrderTagsServiceInterface $service
     */
    public function __construct(PurchaseOrderTagsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase-orders/{id}/tags",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Returns list of tags attached to purchase order",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagListResponse")
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
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTags(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.view');

        return TagListResponse::make($purchaseOrder->tags);
    }

    /**
     * @OA\Post(
     *      path="/finance/purchase-orders/{purchase_order_id}/tags{tag_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to attach a tag to a purchase order",
     *      description="**finance.purchase_orders.manage** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          required=true,
     *          description="Tag identifier",
     *          @OA\Schema(type="integer",example=1)
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
     *         description="Not found. Either purchase order or tag doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Requested tag already assigned to specified purchase order.",
     *      ),
     * )
     * @param PurchaseOrder $purchaseOrder
     * @param Tag           $tag
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function attachTag(PurchaseOrder $purchaseOrder, Tag $tag)
    {
        $this->authorize('finance.purchase_orders.manage');

        $this->service->attachTag($purchaseOrder->id, $tag->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/purchase-orders/{purchase_order_id}/tags{tag_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to detach a tag from a purchase order",
     *      description="``finance.purchase_orders.manage`` permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="purchase_order_id",
     *          in="path",
     *          required=true,
     *          description="Purchase order identifier",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tag_id",
     *          in="path",
     *          required=true,
     *          description="Tag identifier",
     *          @OA\Schema(type="integer",example=1)
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
     *         description="Not found. Either purchase order or tag doesn't exist.",
     *      ),
     * )
     * @param PurchaseOrder $purchaseOrder
     * @param Tag           $tag
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function detachTag(PurchaseOrder $purchaseOrder, Tag $tag)
    {
        $this->authorize('finance.purchase_orders.manage');

        $this->service->detachTag($purchaseOrder->id, $tag->id);

        return ApiOKResponse::make();
    }
}
