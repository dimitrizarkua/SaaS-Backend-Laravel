<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Http\Controllers\Controller;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Events\InvoiceItemsUpdated;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Services\InvoicesService;
use App\Http\Requests\Finance\AddInvoiceItemRequest;
use App\Http\Requests\Finance\UpdateInvoiceItemRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Error\NotAllowedResponse;
use App\Http\Responses\Finance\InvoicesItemResponse;

/**
 * Class InvoiceItemsController
 *
 * @package App\Http\Controllers\Finance
 */
class InvoiceItemsController extends Controller
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
     *      path="/finance/invoices/{invoice_id}/items",
     *      tags={"Finance", "Invoices"},
     *      summary="Create new invoice item",
     *      description="Create new invoice item. **`finance.invoices.manage`** permission is required
    to perform this operation. If invoice has approved status than the additionally **finance.invoices.manage_locked**
    permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="invoice_id",
     *          in="path",
     *          required=true,
     *          description="Invoice identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/AddInvoiceItemRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesItemResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param Invoice               $invoice
     * @param AddInvoiceItemRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Invoice $invoice, AddInvoiceItemRequest $request)
    {
        $this->authorize('finance.invoices.manage');

        if ($invoice->getCurrentStatus() === FinancialEntityStatuses::APPROVED) {
            return new NotAllowedResponse('Invoice already approved.');
        }
        if (true === $invoice->isLocked()) {
            $this->authorize('finance.invoices.manage_locked');
        }

        $data               = $request->validated();
        $data['invoice_id'] = $invoice->id;
        $invoiceItem        = new InvoiceItem($data);
        $invoiceItem->saveOrFail();

        event(new InvoiceItemsUpdated($invoice));

        return InvoicesItemResponse::make($invoiceItem, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/finance/invoices/{invoice_id}/items/{item_id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to update specific invoice item",
     *      description="Allows to update specific invoice item. **`finance.invoices.manage`** permission is required
    to perform this operation. If invoice has approved status than the additionally **finance.invoices.manage_locked**
    permission is required.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateInvoiceItemRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="invoice_id",
     *          in="path",
     *          required=true,
     *          description="Invoice identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="item_id",
     *          in="path",
     *          required=true,
     *          description="Invoice item identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoicesItemResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param UpdateInvoiceItemRequest $request
     * @param Invoice                  $invoice
     * @param InvoiceItem              $invoiceItem
     *
     * @return \App\Http\Responses\ApiResponse
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateInvoiceItemRequest $request, Invoice $invoice, InvoiceItem $invoiceItem)
    {
        $this->authorize('finance.invoices.manage');

        if ($invoice->getCurrentStatus() === FinancialEntityStatuses::APPROVED) {
            return new NotAllowedResponse('Invoice already approved.');
        }
        if (true === $invoice->isLocked()) {
            $this->authorize('finance.invoices.manage_locked');
        }

        $invoiceItem->fillFromRequest($request);

        event(new InvoiceItemsUpdated($invoice));

        return InvoicesItemResponse::make($invoiceItem);
    }

    /**
     * @OA\Delete(
     *      path="/finance/invoices/{invoice_id}/items/{item_id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Delete existing invoice item.",
     *      description="Delete existing invoice item.  **`finance.invoices.manage`** permission is required
    to perform this operation. If invoice has approved status than the additionally **finance.invoices.manage_locked**
    permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="invoice_id",
     *          in="path",
     *          required=true,
     *          description="Invoice identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="item_id",
     *          in="path",
     *          required=true,
     *          description="Invoice item identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested template could not be found.",
     *      ),
     * )
     *
     * @param Invoice     $invoice
     * @param InvoiceItem $invoiceItem
     *
     * @return \App\Http\Responses\ApiResponse
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Invoice $invoice, InvoiceItem $invoiceItem)
    {
        $this->authorize('finance.invoices.manage');

        if ($invoice->getCurrentStatus() === FinancialEntityStatuses::APPROVED) {
            return new NotAllowedResponse('Invoice already approved.');
        }
        if (true === $invoice->isLocked()) {
            $this->authorize('finance.invoices.manage_locked');
        }

        $invoiceItem->delete();

        event(new InvoiceItemsUpdated($invoice));

        return ApiOKResponse::make();
    }
}
