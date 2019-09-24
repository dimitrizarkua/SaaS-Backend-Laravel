<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\VO\ReceivePaymentData;
use App\Components\Finance\Services\InvoicesService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ReceivePaymentRequest;
use App\Http\Responses\ApiOKResponse;

/**
 * Class ReceivePaymentController
 *
 * @package App\Http\Controllers\Finance
 */
class ReceivePaymentController extends Controller
{
    /**
     * @var \App\Components\Finance\Services\InvoicesService
     */
    private $service;

    /**
     * ReceivePaymentController constructor.
     *
     * @param \App\Components\Finance\Services\InvoicesService $service
     */
    public function __construct(InvoicesService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/finance/payments/transfers/receive",
     *     tags={"Finance"},
     *     summary="Receives a payment.",
     *     description="Receive a payment from HQ and allocate the payment to one or many invoices and with
     *     the ability to specify the amount and payment type (DP, FP) for every invoice.
     *     **'finance.payments.transfers.receive'** permission is required to perform this
     *     operation.",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/ReceivePaymentRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden.",
     *     ),
     *     @OA\Response(
     *         response=405,
     *         description="Incorrect payment data.",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     ),
     * )
     *
     * @param \App\Http\Requests\Finance\ReceivePaymentRequest $receivePaymentRequest,
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Throwable
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function receive(ReceivePaymentRequest $receivePaymentRequest)
    {
        $this->authorize('finance.payments.transfers.receive');

        $this->service->receiveInvoicePayment(new ReceivePaymentData(
            [
                'payment_data'      => $receivePaymentRequest->getPaymentData(),
                'invoices_list'     => $receivePaymentRequest->getInvoicesList(),
                'dst_gl_account_id' => $receivePaymentRequest->getDstGLAccountId(),
                'location_id'       => $receivePaymentRequest->getLocationId(),
            ]
        ));

        return ApiOKResponse::make();
    }
}
