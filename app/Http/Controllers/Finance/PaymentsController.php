<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\PaymentsServiceInterface;
use App\Components\Finance\Models\Payment;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Responses\Finance\FullPaymentResponse;
use App\Http\Responses\Finance\PaymentsListResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class PaymentsController
 *
 * @package App\Http\Controllers\Finance
 */
class PaymentsController extends Controller
{
    /**
     * @var \App\Components\Finance\Interfaces\PaymentsServiceInterface
     */
    private $service;

    /**
     * PaymentsController constructor.
     *
     * @param \App\Components\Finance\Interfaces\PaymentsServiceInterface $service
     */
    public function __construct(PaymentsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/payments",
     *      tags={"Finance"},
     *      summary="Get list of payments",
     *      description="Returns list of all payments relevant for locations to which currently authenticated user
    belongs. **`finance.payments.view`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentsListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('finance.payments.view');
        /** @var \App\Models\User $user */
        $user          = Auth::user();
        $userLocations = $user->locations->pluck('id')
            ->toArray();

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $this->service->findPaymentsByLocations($userLocations)
            ->paginate(Paginator::resolvePerPage());

        return PaymentsListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Get(
     *      path="/finance/payments/{id}",
     *      tags={"Finance"},
     *      summary="Returns full information about payment",
     *      description="Returns full information about payment. **`finance.payments.view`** permission is required
    to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullPaymentResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Finance\Models\Payment $payment
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Payment $payment)
    {
        $this->authorize('finance.payments.view');

        return FullPaymentResponse::make($payment);
    }
}
