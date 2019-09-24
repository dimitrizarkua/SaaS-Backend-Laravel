<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Events\NoteAttachedToInvoice;
use App\Components\Finance\Models\Invoice;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Notes\FullNoteListResponse;
use App\Http\Responses\Notes\NoteListResponse;

/**
 * Class InvoiceNotesController
 *
 * @package App\Http\Controllers\Finance
 */
class InvoiceNotesController extends Controller
{
    /**
     * @var UserNotificationsServiceInterface
     */
    private $userNotificationService;

    /**
     * InvoiceNotesController constructor.
     *
     * @param UserNotificationsServiceInterface $userNotificationService
     */
    public function __construct(UserNotificationsServiceInterface $userNotificationService)
    {
        $this->userNotificationService = $userNotificationService;
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/{invoice_id}/notes",
     *      tags={"Finance", "Invoices"},
     *      summary="Returns list of notes attached to specific invoice",
     *      description="Returns list of notes attached to specific invoice.  **`finance.invoices.view`** permission
    is required to perform this operation.",
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
     *         @OA\JsonContent(ref="#/components/schemas/FullNoteListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return ApiOKResponse
     */
    public function getNotes(Invoice $invoice)
    {
        $this->authorize('finance.invoices.view');

        return FullNoteListResponse::make($invoice->notes);
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices/{invoice_id}/notes/{note_id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Attach existing note to specific invoice",
     *      description="Attach existing note to specific invoice.  **`finance.invoices.manage`** permission is required
    to perform this operation.",
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
     *         name="note_id",
     *         in="path",
     *         required=true,
     *         description="Note identifier",
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
     *         description="Note already attached to the invoice",
     *      ),
     * )
     * @param \App\Components\Finance\Models\Invoice $invoice
     * @param \App\Components\Notes\Models\Note      $note
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \ReflectionException
     *
     * @return \App\Http\Responses\ApiOKResponse
     */
    public function attachNote(Invoice $invoice, Note $note)
    {
        $this->authorize('finance.invoices.manage');
        try {
            $invoice->notes()->attach($note);
        } catch (\Exception $e) {
            throw new NotAllowedException('Note already attached to the invoice');
        }

        event(new NoteAttachedToInvoice($invoice, $note));
        $this->userNotificationService->dispatchUserMentionedEvent($invoice, $note, $note->user_id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/invoices/{invoice_id}/notes/{note_id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Detach existing note from specific invoice",
     *      description="Detach existing note from specific invoice.  **`finance.invoices.manage`** permission is
     *      required
    to perform this operation.",
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
     *         name="note_id",
     *         in="path",
     *         required=true,
     *         description="Note identifier",
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
     * @param \App\Components\Notes\Models\Note      $note
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return ApiOKResponse
     */
    public function detachNote(Invoice $invoice, Note $note)
    {
        $this->authorize('finance.invoices.manage');
        $invoice->notes()->detach($note);

        return ApiOKResponse::make();
    }
}
