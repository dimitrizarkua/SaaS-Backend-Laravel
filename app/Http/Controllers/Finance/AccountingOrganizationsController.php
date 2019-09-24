<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\AccountingOrganizationsServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\VO\CreateAccountingOrganizationData;
use App\Components\Locations\Models\Location;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateAccountingOrganizationRequest;
use App\Http\Requests\Finance\UpdateAccountingOrganizationRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Finance\AccountingOrganizationListResponse;
use App\Http\Responses\Finance\AccountingOrganizationResponse;
use App\Http\Responses\Locations\LocationsListResponse;

/**
 * Class AccountingOrganizationsController
 *
 * @package App\Http\Controllers\Finance
 */
class AccountingOrganizationsController extends Controller
{
    /**
     * @var AccountingOrganizationsServiceInterface
     */
    private $service;

    /**
     * AccountingOrganizationsController constructor.
     *
     * @param AccountingOrganizationsServiceInterface $service
     */
    public function __construct(AccountingOrganizationsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/accounting-organizations",
     *      tags={"Finance"},
     *      summary="Get list of accounting organizations",
     *      description="Returns list of accounting organizations. **`finance.accounting_organizations.manage`**
     *      permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountingOrganizationListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('finance.accounting_organizations.manage');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = AccountingOrganization::with([
            'glAccounts',
            'contact',
            'contact.addresses',
            'contact.company',
            'contact.category',
            'contact.statuses',
            'taxPayableAccount',
            'taxReceivableAccount',
            'payableAccount',
            'receivableAccount',
            'paymentDetailsAccount',
        ])->paginate(Paginator::resolvePerPage());

        return AccountingOrganizationListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/finance/accounting-organizations",
     *      tags={"Finance"},
     *      summary="Create new accounting organization",
     *      description="Create new accounting organization. **`finance.accounting_organizations.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateAccountingOrganizationRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/UserProfileResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param CreateAccountingOrganizationRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateAccountingOrganizationRequest $request)
    {
        $this->authorize('finance.accounting_organizations.manage');
        $data  = new CreateAccountingOrganizationData($request->validated());
        $model = $this->service->create($data);

        return AccountingOrganizationResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/accounting-organizations/{id}",
     *      tags={"Finance"},
     *      summary="Returns full information about accounting organization",
     *      description="Returns full information about accounting organization.
     *      **`finance.accounting_organizations.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountingOrganizationResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(AccountingOrganization $accountingOrganization)
    {
        $this->authorize('finance.accounting_organizations.manage');

        return AccountingOrganizationResponse::make($accountingOrganization);
    }

    /**
     * @OA\Patch(
     *      path="/finance/accounting-organizations/{id}",
     *      tags={"Finance"},
     *      summary="Allows to update accounting organization",
     *      description="Allows to update accounting organization. **`finance.accounting_organizations.manage`**
     *      permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateAccountingOrganizationRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountingOrganizationResponse")
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
     * @param UpdateAccountingOrganizationRequest $request
     * @param AccountingOrganization              $accountingOrganization
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateAccountingOrganizationRequest $request, AccountingOrganization $accountingOrganization)
    {
        $this->authorize('finance.accounting_organizations.manage');
        $accountingOrganization->fillFromRequest($request);

        return AccountingOrganizationResponse::make($accountingOrganization);
    }

    /**
     * @OA\Get(
     *      path="/finance/accounting-organizations/{id}/locations",
     *      tags={"Finance"},
     *      summary="Returns list of locations attached to the accounting organization",
     *      description="Returns list of locations attached to the accounting organization.
     **`finance.accounting_organizations.manage`** permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/LocationsListResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getLocations(AccountingOrganization $accountingOrganization)
    {
        $this->authorize('finance.accounting_organizations.manage');

        return LocationsListResponse::make($accountingOrganization->locations);
    }

    /**
     * @OA\Post(
     *      path="/finance/accounting-organizations/{ao_id}/locations/{location_id}",
     *      tags={"Finance"},
     *      summary="Allows to link location to specific accounting organization",
     *      description="Allows to link location to specific accounting organization.
     *      **`finance.accounting_organizations.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="ao_id",
     *          in="path",
     *          required=true,
     *          description="Accounting orgznization identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="location_id",
     *          in="path",
     *          required=true,
     *          description="Location identifier",
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addLocation(AccountingOrganization $accountingOrganization, Location $location)
    {
        $this->authorize('finance.accounting_organizations.manage');
        $this->service->addLocation($accountingOrganization->id, $location->id);

        return ApiOKResponse::make();
    }
}
