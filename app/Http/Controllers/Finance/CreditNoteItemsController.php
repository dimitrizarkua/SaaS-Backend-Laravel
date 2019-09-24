<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Services\CreditNoteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateCreditNoteItemRequest;
use App\Http\Requests\Finance\UpdateCreditNoteItemRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Error\NotAllowedResponse;
use App\Http\Responses\Finance\CreditNoteItemResponse;

/**
 * Class CreditNoteItemsController
 *
 * @package App\Http\Controllers\Finance
 */
class CreditNoteItemsController extends Controller
{
    /**
     * @var CreditNoteService
     */
    private $service;

    /**
     * CreditNotesController constructor.
     *
     * @param CreditNoteService $service
     */
    public function __construct(CreditNoteService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes/{credit_note_id}/items",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Create new credit note item",
     *      description="Create new credit note item. **`finance.credit_notes.manage`** permission is required
    to perform this operation. If credit note already locked than the additionally
     **finance.credit_notes.manage_locked** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateCreditNoteItemRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteItemResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Finance\CreateCreditNoteItemRequest $request
     *
     * @param \App\Components\Finance\Models\CreditNote              $creditNote
     *
     * @return \App\Http\Responses\ApiResponse|\App\Http\Responses\Finance\CreditNoteItemResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateCreditNoteItemRequest $request, CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.manage');

        if ($creditNote->getCurrentStatus() === FinancialEntityStatuses::APPROVED) {
            return new NotAllowedResponse('Credit note already approved.');
        }

        if ($creditNote->isLocked()) {
            $this->authorize('finance.credit_notes.manage_locked');
        }
        $data               = $request->validated();
        $data['credit_note_id'] = $creditNote->id;
        $item = CreditNoteItem::create($data);
        $item->saveOrFail();

        return CreditNoteItemResponse::make($item, null, 201);
    }

    /**
     * @OA\Patch(
     *      path="/finance/credit-notes/{credit_note_id}/items/{credit_note_item_id}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to update specific credit note item",
     *      description="Allows to update specific credit note item. **`finance.credit_notes.manage`** permission is
    required to perform this operation. If credit note already locked than the additionally
     **finance.credit_notes.manage_locked** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateCreditNoteItemRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteItemResponse")
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
     * @param UpdateCreditNoteItemRequest                   $request
     * @param \App\Components\Finance\Models\CreditNote     $creditNote
     * @param \App\Components\Finance\Models\CreditNoteItem $creditNoteItem
     *
     * @return \App\Http\Responses\ApiResponse|\App\Http\Responses\Finance\CreditNoteItemResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateCreditNoteItemRequest $request, CreditNote $creditNote, CreditNoteItem $creditNoteItem)
    {
        $this->authorize('finance.credit_notes.manage');

        if ($creditNote->getCurrentStatus() === FinancialEntityStatuses::APPROVED) {
            return new NotAllowedResponse('Credit note already approved.');
        }

        if ($creditNote->isLocked()) {
            $this->authorize('finance.credit_notes.manage_locked');
        }

        $creditNoteItem->fillFromRequest($request);

        return CreditNoteItemResponse::make($creditNoteItem);
    }

    /**
     * @OA\Delete(
     *      path="/finance/credit-notes/{credit_note_id}/items/{credit_note_item_id}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Remove a credit note",
     *      description="Allows to remove a credit note item. **`finance.credit_notes.manage`** permission is required
    to perform this operation. If credit note already locked than the additionally
     **finance.credit_notes.manage_locked** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Either job or note doesn't exist.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Could not make changes to the locked credit note.",
     *      ),
     * )
     *
     * @param \App\Components\Finance\Models\CreditNote     $creditNote
     * @param \App\Components\Finance\Models\CreditNoteItem $creditNoteItem
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(CreditNote $creditNote, CreditNoteItem $creditNoteItem)
    {
        $this->authorize('finance.credit_notes.manage');

        if ($creditNote->getCurrentStatus() === FinancialEntityStatuses::APPROVED) {
            return new NotAllowedResponse('Credit note already approved.');
        }

        if ($creditNote->isLocked()) {
            $this->authorize('finance.credit_notes.manage_locked');
        }

        $creditNoteItem->delete();

        return ApiOKResponse::make();
    }
}
