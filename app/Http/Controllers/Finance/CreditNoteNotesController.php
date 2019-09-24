<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Events\NoteAttachedToCreditNote;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Services\CreditNoteService;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Interfaces\UserNotificationsServiceInterface;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Notes\FullNoteListResponse;

/**
 * Class CreditNoteNotesController
 *
 * @package App\Http\Controllers\Finance
 */
class CreditNoteNotesController extends Controller
{
    /**
     * @var CreditNoteService
     */
    private $service;

    /**
     * @var UserNotificationsServiceInterface
     */
    private $userNotificationService;

    /**
     * CreditNoteNotesController constructor.
     *
     * @param CreditNoteService                 $service
     * @param UserNotificationsServiceInterface $userNotificationsService
     */
    public function __construct(CreditNoteService $service, UserNotificationsServiceInterface $userNotificationsService)
    {
        $this->service                 = $service;
        $this->userNotificationService = $userNotificationsService;
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/{credit_note}/notes",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns list of notes attached to specific Credit note.",
     *      description="Returns list of notes attached to specific Credit note in reverse chronological order.
     **`finance.credit_notes.view`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="credit_note",
     *          in="path",
     *          required=true,
     *          description="Credit note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullNoteListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getNotes(CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.view');

        $result = $creditNote->notes()->with('documents', 'user', 'user.avatar')->get();

        return FullNoteListResponse::make($result);
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes/{credit_note}/notes/{note}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to attach note to specific credit note",
     *      description="Allows to attach note to specific credit note. **`finance.credit_notes.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="credit_note",
     *          in="path",
     *          required=true,
     *          description="Credit note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="note",
     *          in="path",
     *          required=true,
     *          description="Attached note identifier",
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
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @param \App\Components\Notes\Models\Note         $note
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \ReflectionException
     */
    public function attachNote(CreditNote $creditNote, Note $note)
    {
        $this->authorize('finance.credit_notes.manage');

        try {
            $creditNote->notes()->attach($note);
        } catch (\Exception $e) {
            throw new NotAllowedException('Note already attached to the invoice');
        }

        event(new NoteAttachedToCreditNote($creditNote, $note));
        $this->userNotificationService->dispatchUserMentionedEvent($creditNote, $note, $note->user_id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/credit-notes/{id}/notes/{note}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to detach note from specific credit note",
     *      description="Allows to detach note from specific credit note. **`finance.credit_notes.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="credit_note",
     *          in="path",
     *          required=true,
     *          description="Credit note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="note",
     *          in="path",
     *          required=true,
     *          description="Attached note identifier",
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
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @param \App\Components\Notes\Models\Note         $note
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachNote(CreditNote $creditNote, Note $note)
    {
        $this->authorize('finance.credit_notes.manage');

        $creditNote->notes()->detach($note);

        return ApiOKResponse::make();
    }
}
