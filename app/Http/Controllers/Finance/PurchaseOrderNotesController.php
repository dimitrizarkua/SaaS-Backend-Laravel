<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Notes\Models\Note;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Notes\FullNoteListResponse;

/**
 * Class PurchaseOrderNotesController
 *
 * @package App\Http\Controllers\Finance
 */
class PurchaseOrderNotesController extends Controller
{
    /**
     * @var \App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface
     */
    protected $service;

    /**
     * PurchaseOrderNotesController constructor.
     *
     * @param \App\Components\Finance\Interfaces\PurchaseOrderNotesServiceInterface $service
     */
    public function __construct(PurchaseOrderNotesServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/purchase_orders/{id}/notes",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Returns list of notes attached to specified purchase order",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullNoteListResponse")
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
     * @throws \Throwable
     */
    public function getNotes(PurchaseOrder $purchaseOrder)
    {
        $this->authorize('finance.purchase_orders.view');

        $result = $purchaseOrder->notes()->with('documents', 'user', 'user.avatar')->get();

        return FullNoteListResponse::make($result);
    }

    /**
     * @OA\Post(
     *      path="/finance/purchase-orders/{purchase_order_id}/notes/{note_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to attach a note to a purchase order",
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
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
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
     *         response=404,
     *         description="Not found. Either purchase order or note doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Note is already attached to this purchase order.",
     *      ),
     * )
     *
     * @param int  $purchaseOrderId
     * @param Note $note
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     */
    public function attachNote(int $purchaseOrderId, Note $note)
    {
        $this->authorize('finance.purchase_orders.manage');
        $this->authorize('attach', $note);

        $this->service->attachNote($purchaseOrderId, $note->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/purchase-orders/{purchase_order_id}/notes/{note_id}",
     *      tags={"Finance", "Purchase Orders"},
     *      summary="Allows to detach a note from a purchase order",
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
     *          name="note_id",
     *          in="path",
     *          required=true,
     *          description="Note identifier",
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
     *         response=404,
     *         description="Not found. Either purchase order or note doesn't exist.",
     *      ),
     * )
     *
     * @param int  $purchaseOrderId
     * @param Note $note
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachNote(int $purchaseOrderId, Note $note)
    {
        $this->authorize('finance.purchase_orders.manage');
        $this->authorize('detach', $note);

        $this->service->detachNote($purchaseOrderId, $note->id);

        return ApiOKResponse::make();
    }
}
