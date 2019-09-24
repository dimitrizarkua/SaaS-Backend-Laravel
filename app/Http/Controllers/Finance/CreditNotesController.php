<?php

namespace App\Http\Controllers\Finance;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\VO\CreateCreditNoteData;
use App\Components\Finance\Services\CreditNoteService;
use App\Exceptions\Api\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateApprovalRequestRequest;
use App\Http\Requests\Finance\CreateCreditNoteRequest;
use App\Http\Requests\Finance\CreatePaymentByCreditNoteRequest;
use App\Http\Requests\Finance\UpdateCreditNoteRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\ApproverListResponse;
use App\Http\Responses\Finance\CreditNoteApproveRequestResponse;
use App\Http\Responses\Finance\CreditNoteResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class CreditNotesController
 *
 * @package App\Http\Controllers\Finance
 */
class CreditNotesController extends Controller
{
    /**
     * @var CreditNoteService
     */
    private $service;
    /**
     * @var DocumentsServiceInterface
     */
    private $documentsService;

    /**
     * CreditNotesController constructor.
     *
     * @param CreditNoteService $service
     */
    public function __construct(CreditNoteService $service, DocumentsServiceInterface $documentsService)
    {
        $this->service = $service;
        $this->documentsService = $documentsService;
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Create new credit note",
     *      description="Create new credit note. **`finance.credit_notes.manage`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateCreditNoteRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Finance\CreateCreditNoteRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function store(CreateCreditNoteRequest $request)
    {
        $this->authorize('finance.credit_notes.manage');
        $model = new CreateCreditNoteData($request->validated());

        $creditNote = $this->service->create($model, Auth::id());

        return CreditNoteResponse::make($creditNote, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/{id}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns full information about credit note",
     *      description="Returns full information about credit note. **`finance.credit_notes.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $creditNoteId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $creditNoteId)
    {
        $this->authorize('finance.credit_notes.view');
        $creditNote = CreditNote::findOrFail($creditNoteId);

        return CreditNoteResponse::make($creditNote);
    }

    /**
     * @OA\Patch(
     *      path="/finance/credit-notes/{id}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to update specific credit note",
     *      description="Allows to update specific credit note. **`finance.credit_notes.manage`** permission is required
    to perform this operation. If credit note already locked than the additionally
     **finance.credit_notes.manage_locked** permission is required.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateCreditNoteRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteResponse")
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
     * @param UpdateCreditNoteRequest $request
     * @param CreditNote              $creditNote
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateCreditNoteRequest $request, CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.manage');

        $forceUpdate = false;
        if ($creditNote->isLocked()) {
            $this->authorize('finance.credit_notes.manage_locked');
            $forceUpdate = true;
        }

        $updatedEntity = $this->service->update($creditNote->id, $request->validated(), $forceUpdate);

        return CreditNoteResponse::make($updatedEntity);
    }

    /**
     * @OA\Delete(
     *      path="/finance/credit-notes/{id}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Remove a credit note",
     *      description="Allows to remove a credit note. **`finance.credit_notes.manage`** permission is required
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
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function destroy(CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.manage');

        if ($creditNote->isLocked()) {
            $this->authorize('finance.credit_notes.manage_locked');
        }
        $this->service->delete($creditNote->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes/{credit_note}/approve",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Approve credit note",
     *      description="Allows to approve credit note. **`finance.credit_notes.manage`** permission is required
    to perform this operation.",
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
     * @param int $creditNoteId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function approve(int $creditNoteId)
    {
        $this->authorize('finance.credit_notes.manage');

        $this->service->approve($creditNoteId, Auth::user());

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes/{credit_note}/payment",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Create payment by credit note. **`finance.credit_notes.manage`** permission is required
    to perform this operation.",
     *      description="Allows to create payment by credit note",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreatePaymentByCreditNoteRequest")
     *          )
     *      ),
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
     * @param \App\Http\Requests\Finance\CreatePaymentByCreditNoteRequest $request
     *
     * @param \App\Components\Finance\Models\CreditNote                   $creditNote
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function createPayment(CreatePaymentByCreditNoteRequest $request, CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.manage');

        $this->service->createPaymentForCreditNote($request->getPaymentItems(), $creditNote);

        return ApiOKResponse::make();
    }


    /**
     * @OA\Get(
     *      path="/finance/credit-notes/{id}/approve-requests",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to get list of approve requests for specific credit note",
     *      description="Allows to get approve requests for specific credit note. **`finance.credit_notes.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="id",
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
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param int $creditNoteId
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Finance\CreditNoteApproveRequestResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getApproveRequests(int $creditNoteId)
    {
        $this->authorize('finance.credit_notes.view');

        return CreditNoteApproveRequestResponse::make(CreditNote::find($creditNoteId)->approveRequests);
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes/{id}/approve-requests",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to send approve requests for specific credit note",
     *      description="Allows to send approve requests for specific credit note. **`finance.credit_notes.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateApprovalRequestRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param int                                                     $creditNoteId
     *
     * @param \App\Http\Requests\Finance\CreateApprovalRequestRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function addApproveRequests(int $creditNoteId, CreateApprovalRequestRequest $request)
    {
        $this->authorize('finance.credit_notes.manage');

        $this->service->createApproveRequest(
            $creditNoteId,
            $request->user()->id,
            $request->getApproverIdsList()
        );

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/{id}/approver-list",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns list of users that are able to approve specific credit note",
     *      description="Returns list of users that are able to approve specific credit note. **
    `finance.credit_notes.view`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ApproverListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param CreditNote $creditNote
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function approverList(CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.view');

        $list = $this->service->getApproversList($creditNote->id);

        return ApproverListResponse::make($list);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/{id}/document",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to download PDF document generated for specific credit note",
     *      description="Allows to download PDF document generated for specific credit
    note.  **`finance.credit_notes.view`** permission is required to perform this operation.",
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
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param CreditNote $creditNote
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDocument(CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.view');
        if (null === $creditNote->document_id) {
            throw new NotFoundException('A printed version of this credit note doesn\'t exist.');
        }

        return $this->documentsService->getDocumentContentsAsResponse($creditNote->document_id);
    }
}
