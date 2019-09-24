<?php

namespace App\Http\Controllers\UsageAndActuals;

use App\Components\Contacts\Models\Contact;
use App\Components\Pagination\Paginator;
use App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface;
use App\Components\UsageAndActuals\Models\InsurerContract;
use App\Components\UsageAndActuals\Models\VO\InsurerContractData;
use App\Http\Controllers\Controller;
use App\Http\Requests\UsageAndActuals\CreateInsurerContractRequest;
use App\Http\Requests\UsageAndActuals\UpdateInsurerContractRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\UsageAndActuals\FullInsurerContractResponse;
use App\Http\Responses\UsageAndActuals\InsurerContractListResponse;

/**
 * Class InsurerContractsController
 *
 * @package App\Http\Controllers\UsageAndActuals
 */
class InsurerContractsController extends Controller
{
    /**
     * @var \App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface
     */
    private $service;

    /**
     * InsurerContractsController constructor.
     *
     * @param \App\Components\UsageAndActuals\Interfaces\InsurerContractsInterface $service
     */
    public function __construct(InsurerContractsInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/usage-and-actuals/insurer-contracts",
     *      tags={"Usage and Actuals"},
     *      summary="Create new insurer contract",
     *      description="Create new insurer contract **`usage_and_actuals.insurer_contracts.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateInsurerContractRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullInsurerContractResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\UsageAndActuals\CreateInsurerContractRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\UsageAndActuals\FullInsurerContractResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function store(CreateInsurerContractRequest $request)
    {
        $this->authorize('usage_and_actuals.insurer_contracts.manage');

        $data            = new InsurerContractData($request->validated());
        $insurerContract = $this->service->createContract($data);

        return FullInsurerContractResponse::make($insurerContract, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/insurer-contracts/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Returns full information about insurer contract",
     *      description="Returns full information about insurer contract **`usage_and_actuals.insurer_contracts.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullInsurerContractResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\UsageAndActuals\Models\InsurerContract $insurerContract
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(InsurerContract $insurerContract)
    {
        $this->authorize('usage_and_actuals.insurer_contracts.view');

        return FullInsurerContractResponse::make($insurerContract);
    }

    /**
     * @OA\Patch(
     *      path="/usage-and-actuals/insurer-contracts/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Update existing insurer contract",
     *      description="Update existing insurer contract **`usage_and_actuals.insurer_contracts.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateInsurerContractRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullInsurerContractResponse")
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
     *
     * @param \App\Http\Requests\UsageAndActuals\UpdateInsurerContractRequest $request
     * @param \App\Components\UsageAndActuals\Models\InsurerContract          $insurerContract
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\UsageAndActuals\FullInsurerContractResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function update(UpdateInsurerContractRequest $request, InsurerContract $insurerContract)
    {
        $this->authorize('usage_and_actuals.insurer_contracts.manage');

        $data            = new InsurerContractData($request->validated());
        $insurerContract = $this->service->updateContract($insurerContract, $data);

        return FullInsurerContractResponse::make($insurerContract);
    }

    /**
     * @OA\Delete(
     *      path="/usage-and-actuals/insurer-contracts/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Delete existing insurer contract",
     *      description="Delete existing insurer contract **`usage_and_actuals.insurer_contracts.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
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
     *
     * @param \App\Components\UsageAndActuals\Models\InsurerContract $insurerContract
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(InsurerContract $insurerContract)
    {
        $this->authorize('usage_and_actuals.insurer_contracts.manage');

        $this->service->deleteContract($insurerContract->id);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/insurer-contracts/contracts/{id}",
     *      tags={"Usage and Actuals"},
     *      summary="Returns list of contracts for specific insurer",
     *      description="Returns list of contracts for specific insurer **`usage_and_actuals.insurer_contracts.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/InsurerContractListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $insurerId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getContracts(int $insurerId)
    {
        $this->authorize('usage_and_actuals.insurer_contracts.view');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = InsurerContract::query()
            ->where('contact_id', $insurerId)
            ->paginate(Paginator::resolvePerPage());

        return InsurerContractListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Get(
     *      path="/usage-and-actuals/insurer-contracts/contracts/{id}/active",
     *      tags={"Usage and Actuals"},
     *      summary="Returns active contract for specific insurer",
     *      description="Returns active contract for specific insurer **`usage_and_actuals.insurer_contracts.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/FullInsurerContractResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $insurerId
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getActiveContract(int $insurerId)
    {
        $this->authorize('usage_and_actuals.insurer_contracts.view');

        $insurerContract = $this->service->getActiveContractForInsurer(Contact::find($insurerId));

        return FullInsurerContractResponse::make($insurerContract);
    }
}
