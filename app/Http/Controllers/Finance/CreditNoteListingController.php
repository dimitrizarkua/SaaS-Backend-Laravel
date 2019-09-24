<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\CreditNotesListingServiceInterface;
use App\Components\Finance\Models\CreditNote;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\GetCreditNoteRequest;
use App\Http\Requests\Finance\GetInfoRequest;
use App\Http\Requests\Finance\SearchCreditNotesRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\CreditNoteInfoResponse;
use App\Http\Responses\Finance\CreditNoteListResponse;
use App\Http\Responses\Finance\CreditNotesSearchResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteListingController
 *
 * @package App\Http\Controllers\Finance
 */
class CreditNoteListingController extends Controller
{
    /**
     * @var CreditNotesListingServiceInterface
     */
    private $listingService;

    /**
     * PurchaseOrderListingController constructor.
     *
     * @param CreditNotesListingServiceInterface $service
     */
    public function __construct(CreditNotesListingServiceInterface $listingService)
    {
        $this->listingService = $listingService;
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/listings/info",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns counters and total amount by statuses",
     *      description="Returns counters and total amount by statuses. **`finance.credit_notes.view`** permission is
    required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter counters by location ids. If empty then locations belongs to authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteInfoResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getInfo(GetInfoRequest $request)
    {
        $this->authorize('finance.credit_notes.view');

        $info = $this->listingService->getInfo($request->getLocationIds());

        return CreditNoteInfoResponse::make($info);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/listings/draft",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns list of draft credit notes",
     *      description="Returns list of draft credit notes. **`finance.credit_notes.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter credit notes list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter credit notes by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter credit notes by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\GetCreditNoteRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function getDraft(GetCreditNoteRequest $request)
    {
        $this->authorize('finance.credit_notes.view');

        $items = $this->listingService->getDraft($request->getCreditNoteListingFilter());

        return CreditNoteListResponse::make($items);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/listings/pending-approval",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns list of draft credit notes with approval requests",
     *      description="Returns list of draft credit notes with approval requests. **`finance.credit_notes.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter credit notes list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter credit notes by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter credit notes by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\GetCreditNoteRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function getPendingApproval(GetCreditNoteRequest $request)
    {
        $this->authorize('finance.credit_notes.view');

        $items = $this->listingService->getPendingApproval($request->getCreditNoteListingFilter());

        return CreditNoteListResponse::make($items);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/listings/approved",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns list of approved credit notes",
     *      description="Returns list of approved credit notes. **`finance.credit_notes.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter credit notes list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter credit notes by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter credit notes by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\GetCreditNoteRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function getApproved(GetCreditNoteRequest $request)
    {
        $this->authorize('finance.credit_notes.view');

        $items = $this->listingService->getApproved($request->getCreditNoteListingFilter());

        return CreditNoteListResponse::make($items);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/listings/all",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Returns whole list of credit notes",
     *      description="Returns whole list of credit notes. **`finance.credit_notes.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="path",
     *         description="Allows to filter credit notes list by location ids",
     *         @OA\Schema(
     *          type="array",
     *          @OA\Items(
     *              type="integer",
     *              example=1
     *          ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="recipient_contact_id",
     *         in="path",
     *         description="Allows to filter credit notes by contact",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_from",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="date_to",
     *         in="path",
     *         description="Allows to filter credit notes by date",
     *         @OA\Schema(
     *              type="string",
     *              format="date_from",
     *              example="2019-01-10"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="job_id",
     *         in="path",
     *         description="Allows to filter credit notes by job",
     *         @OA\Schema(
     *              type="integer",
     *              example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/CreditNoteListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\GetCreditNoteRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function getAll(GetCreditNoteRequest $request)
    {
        $this->authorize('finance.credit_notes.view');

        $items = $this->listingService
            ->getAll($request->getCreditNoteListingFilter())
            ->get();

        return CreditNoteListResponse::make($items);
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/listings/search",
     *      summary="Allows to search credit notes for numbers (id prefix)",
     *      description="**finance.purchase_orders.view** permission is required to perform this operation.",
     *      tags={"Finance", "Credit Notes", "Search"},
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          required=true,
     *          description="Number of purchase orders (id prefix)",
     *          @OA\Schema(
     *              type="string",
     *              example="10",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter search results by location ids. If empty then locations belongs to
     *         authenticated
    user will be used.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="virtual_status",
     *         in="query",
     *         description="Allows to filter search results by virtual status",
     *         @OA\Schema(
     *              ref="#/components/schemas/CreditNoteVirtualStatuses"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Allows to filter search results by real status",
     *         @OA\Schema(
     *              ref="#/components/schemas/FinancialEntityStatuses"
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="Matching purchase orders",
     *         @OA\JsonContent(ref="#/components/schemas/PurchaseOrderSearchResponse"),
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *      )
     * )
     * @param SearchCreditNotesRequest $request
     *
     * @return ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchCreditNotesRequest $request)
    {
        $this->authorize('finance.purchase_orders.view');

        $response = CreditNote::searchForNumbers(
            $request->getOptions(),
            $request->getLocationIds(),
            $request->getVirtualStatus(),
            $request->getStatus()
        );

        return new CreditNotesSearchResponse($response);
    }
}
