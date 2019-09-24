<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface;
use App\Components\Finance\Models\VO\ForwardedPaymentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ForwardedPaymentRequest;
use App\Http\Responses\ApiOKResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class ForwardedPaymentsController
 *
 * @package App\Http\Controllers\Finance
 */
class ForwardedPaymentsController extends Controller
{
    /**
     * @var \App\Components\Finance\Services\ForwardedPaymentsService
     */
    private $service;

    /**
     * ForwardedPaymentsController constructor.
     *
     * @param \App\Components\Finance\Interfaces\ForwardedPaymentsServiceInterface $service
     */
    public function __construct(ForwardedPaymentsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/finance/payments/forward",
     *      tags={"Finance"},
     *      summary="Forward a funds from headquarter to branch/franchise. **finance.payments.forward** permission is
     *      required to perform this operation.", description="Allocate invoices marked as forwarded to payment and do
     *      forwarding funds from one account to another.", security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ForwardedPaymentRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden.",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not Allowed. Requested payment could not be forwarded.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\ForwardedPaymentRequest $forwardPaymentRequest
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     * @throws \Throwable
     */
    public function forward(ForwardedPaymentRequest $forwardPaymentRequest)
    {
        $this->authorize('finance.payments.forward');
        $forwardedPaymentData = new ForwardedPaymentData($forwardPaymentRequest->validated());
        $forwardedPaymentData->setUserId(Auth::id());
        $this->service->forward($forwardedPaymentData);

        return ApiOKResponse::make();
    }
}
