<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\Invoice;
use App\Components\Tags\Models\Tag;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Tags\TagListResponse;

/**
 * Class InvoiceTagsController
 *
 * @package App\Http\Controllers\Finance
 */
class InvoiceTagsController extends Controller
{
    /**
     * @OA\Get(
     *      path="/finance/invoices/{invoice_id}/tags",
     *      tags={"Finance", "Invoices"},
     *      summary="Returns list of tags attached to specific invoice",
     *      description="Returns list of tags attached to specific invoice. **`finance.invoices.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="invoice_id",
     *         in="path",
     *         required=true,
     *         description="Invoice identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return ApiOKResponse
     */
    public function getTags(Invoice $invoice)
    {
        $this->authorize('finance.invoices.view');

        return TagListResponse::make($invoice->tags);
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices/{invoice_id}/tags/{tag_id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Attach existing tag to specific invoice",
     *      description="Attach existing tag to specific invoice. **`finance.invoices.manage`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="invoice_id",
     *         in="path",
     *         required=true,
     *         description="Invoice identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="Tag identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Tag already attached to the invoice",
     *      ),
     * )
     * @param \App\Components\Finance\Models\Invoice $invoice
     * @param \App\Components\Tags\Models\Tag        $tag
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \App\Http\Responses\ApiOKResponse
     */
    public function attachTag(Invoice $invoice, Tag $tag)
    {
        $this->authorize('finance.invoices.manage');
        try {
            $invoice->tags()->attach($tag);
        } catch (\Exception $e) {
            throw new NotAllowedException('Tag already attached to the invoice');
        }

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/invoices/{invoice_id}/tags/{tag_id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Detach existing tag from specific invoice",
     *      description="Detach existing tag from specific invoice. **`finance.invoices.manage`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="invoice_id",
     *         in="path",
     *         required=true,
     *         description="Invoice identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="tag_id",
     *         in="path",
     *         required=true,
     *         description="Tag identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Finance\Models\Invoice $invoice
     * @param \App\Components\Tags\Models\Tag        $tag
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return ApiOKResponse
     */
    public function detachTag(Invoice $invoice, Tag $tag)
    {
        $this->authorize('finance.invoices.manage');
        $invoice->tags()->detach($tag);

        return ApiOKResponse::make();
    }
}
