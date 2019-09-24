<?php

namespace App\Http\Controllers\Finance;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Finance\CreditCardPaymentProcessor;
use App\Components\Finance\Exceptions\NotAllowedException;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\VO\CreateInvoiceData;
use App\Components\Finance\Models\VO\DirectDepositPaymentData;
use App\Components\Finance\Services\InvoicesService;
use App\Exceptions\Api\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateCreditCardPaymentsRequest;
use App\Http\Requests\Finance\CreateInvoiceRequest;
use App\Http\Requests\Finance\DirectDepositPaymentRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\FullPaymentResponse;
use App\Http\Responses\Finance\InvoiceResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class InvoicesController
 *
 * @package App\Http\Controllers\Finance
 */
class InvoicesController extends Controller
{
    /**
     * @var InvoicesService
     */
    private $invoicesService;

    /**
     * @var DocumentsServiceInterface
     */
    private $documentsService;

    /**
     * InvoicesController constructor.
     *
     * @param InvoicesService           $invoicesService
     * @param DocumentsServiceInterface $documentsService
     */
    public function __construct(InvoicesService $invoicesService, DocumentsServiceInterface $documentsService)
    {
        $this->invoicesService  = $invoicesService;
        $this->documentsService = $documentsService;
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices",
     *      tags={"Finance", "Invoices"},
     *      summary="Create new invoice",
     *      description="Create new invoice.  **`finance.invoices.manage`** permission is required to perform this
    operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateInvoiceRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param CreateInvoiceRequest $request
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     *
     * @return ApiOKResponse
     */
    public function store(CreateInvoiceRequest $request)
    {
        $this->authorize('finance.invoices.manage');
        $createInvoiceData = new CreateInvoiceData($request->validated());
        $invoice           = $this->invoicesService->create($createInvoiceData, Auth::id());

        return InvoiceResponse::make($invoice, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/{id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Returns full information about invoice",
     *      description="Returns full information about invoice.  **`finance.invoices.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \App\Http\Responses\ApiResponse
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('finance.invoices.view');

        return InvoiceResponse::make($invoice);
    }

    /**
     * @OA\Get(
     *      path="/finance/invoices/{id}/document",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to download PDF document generated for specific invoice",
     *      description="Allows to download PDF document generated for specific invoice.  **`finance.invoices.view`**
    permission is required to perform this operation.",
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
     * @param Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function document(Invoice $invoice)
    {
        $this->authorize('finance.invoices.view');
        if (null === $invoice->document_id) {
            throw new NotFoundException('A printed version of this invoice doesn\'t exist.');
        }

        return $this->documentsService->getDocumentContentsAsResponse($invoice->document_id);
    }

    /**
     * @OA\Patch(
     *      path="/finance/invoices/{id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to update invoice",
     *      description="Allows to update invoice.  **`finance.invoices.manage`** permission is required to perform
    this operation. If invoice has approved status than the additionally **finance.invoices.manage_locked**
    permission is required.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateInvoiceRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
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
     * @param UpdateInvoiceRequest $request
     * @param Invoice              $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     *
     * @return ApiOKResponse
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->authorize('finance.invoices.manage');

        $forceUpdate = false;
        if (true === $invoice->isLocked()) {
            $this->authorize('finance.invoices.manage_locked');
            $forceUpdate = true;
        }

        $updatedModel = $this->invoicesService->update($invoice->id, $request->validated(), $forceUpdate);

        return InvoiceResponse::make($updatedModel);
    }

    /**
     * @OA\Delete(
     *      path="/finance/invoices/{id}",
     *      tags={"Finance", "Invoices"},
     *      summary="Delete existing invoice. **`finance.invoices.manage`** permission is required to perform
    this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested template could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Invoice cannot be deleted",
     *      ),
     * )
     * @param Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     * @throws \Throwable
     *
     * @return \App\Http\Responses\ApiOKResponse
     */
    public function destroy(Invoice $invoice)
    {
        $this->authorize('finance.invoices.manage');
        $this->invoicesService->delete($invoice->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices/{id}/approve",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to approve an invoice",
     *      description="Allows to approve an invoice. **`finance.invoices.manage`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
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
     *         description="Unable to change invoice status.",
     *      ),
     * )
     * @param Invoice $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     *
     * @return ApiOKResponse
     */
    public function approve(Invoice $invoice)
    {
        $this->authorize('finance.invoices.manage');
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $this->invoicesService->approve($invoice->id, $user);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices/{invoice_id}/payments/receive/credit-card",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to receive payment with credit card",
     *      description="Allows to receive an invoice. **`finance.payments.receive`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="invoice_id",
     *          in="path",
     *          required=true,
     *          description="Invoice identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateCreditCardPaymentsRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullPaymentResponse")
     *      ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *     @OA\Response(
     *         response=405,
     *         description="Unable to perform operation.",
     *         @OA\JsonContent(ref="#/components/schemas/NotAllowedResponse")
     *      ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=424,
     *          description="Failed dependency error",
     *          @OA\JsonContent(ref="#/components/schemas/FailedDependencyResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\CreateCreditCardPaymentsRequest $request
     * @param Invoice                                                    $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     *
     * @return \App\Http\Responses\Finance\FullPaymentResponse
     */
    public function receiveCreditCardPayment(CreateCreditCardPaymentsRequest $request, Invoice $invoice)
    {
        $this->authorize('finance.payments.receive');

        $creditCard = $request->getCreditCard();

        $createInvoicePaymentData = app()->make(CreditCardPaymentProcessor::class)
            ->setCreditCard($creditCard)
            ->setInvoice($invoice)
            ->createInvoicePaymentData(auth()->id());

        $payment = $this->invoicesService->payWithCreditCard($createInvoicePaymentData);

        return new FullPaymentResponse($payment);
    }

    /**
     * @OA\Post(
     *      path="/finance/invoices/{invoice_id}/payments/receive/direct-deposit",
     *      tags={"Finance", "Invoices"},
     *      summary="Allows to receive payment with direct deposit",
     *      description="Allows to receive payment with direct deposit for an invoice. **`finance.payments.receive`**
     *      permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/DirectDepositPaymentRequest")
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="invoice_id",
     *          in="path",
     *          required=true,
     *          description="Invoice identifier.",
     *          @OA\Schema(type="integer",example=1)
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullPaymentResponse")
     *      ),
     *      @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Unable to perform operation.",
     *      ),
     * )
     *
     * @param DirectDepositPaymentRequest $request
     * @param Invoice                     $invoice
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     * @throws \JsonMapper_Exception
     * @throws NotAllowedException
     *
     * @return \App\Http\Responses\Finance\FullPaymentResponse
     */
    public function receiveDirectDepositPayment(DirectDepositPaymentRequest $request, Invoice $invoice)
    {
        $this->authorize('finance.payments.receive');

        $invoicePaymentData = (new DirectDepositPaymentData($request->validated()))
            ->setInvoice($invoice)
            ->setUser($request->user())
            ->getInvoicePaymentsData();

        $payment = $this->invoicesService->payWithDirectDepositPayment($invoicePaymentData);

        return new FullPaymentResponse($payment);
    }
}
