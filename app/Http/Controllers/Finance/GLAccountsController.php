<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Interfaces\GLAccountServiceInterface;
use App\Components\Finance\Models\AccountingOrganization;
use App\Components\Finance\Models\Filters\GLAccountFilter;
use App\Components\Finance\Models\GLAccount;
use App\Components\Pagination\Paginator;
use App\Components\Search\Models\GLAccountView;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateGLAccountRequest;
use App\Http\Requests\Finance\GLAccountsIndexRequest;
use App\Http\Requests\Finance\SearchGLAccountRequest;
use App\Http\Requests\Finance\UpdateGLAccountRequest;
use App\Http\Responses\Finance\GLAccountListResponse;
use App\Http\Responses\Finance\GLAccountResponse;
use App\Http\Responses\Finance\GLAccountSearchListResponse;

/**
 * Class GLAccountsController
 *
 * @package App\Http\Controllers\Finance
 */
class GLAccountsController extends Controller
{
    /**
     * @var \App\Components\Finance\Interfaces\GLAccountServiceInterface
     */
    protected $glAccountService;

    /**
     * GLAccountsController constructor.
     *
     * @param \App\Components\Finance\Interfaces\GLAccountServiceInterface $glAccountService
     */
    public function __construct(GLAccountServiceInterface $glAccountService)
    {
        $this->glAccountService = $glAccountService;
    }

    /**
     * @OA\Get(
     *      path="/finance/accounting-organizations/{accounting_organization_id}/gl-accounts",
     *      tags={"Finance"},
     *      summary="Get list of GL Accounts for specified accounting organization.",
     *      description="Returns list of GL Accounts. **`finance.gl_accounts.view`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="accounting_organization_id",
     *          in="path",
     *          required=true,
     *          description="Accounting organization identifier.",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="is_debit",
     *          in="query",
     *          description="Allows to filter by account type",
     *          @OA\Schema(
     *             type="boolean",
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountListResponse"),
     *       ),
     *     )
     *
     * @param \App\Components\Finance\Models\AccountingOrganization $accountingOrganization
     * @param \App\Http\Requests\Finance\GLAccountsIndexRequest     $request
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Finance\GLAccountListResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(GLAccountsIndexRequest $request, AccountingOrganization $accountingOrganization)
    {
        $this->authorize('finance.gl_accounts.view');

        $query = GLAccount::query()
            ->with('taxRate', 'accountType')
            ->where('accounting_organization_id', $accountingOrganization->id);

        if (null !== $request->is_debit) {
            $query
                ->leftJoin('account_types', 'account_types.id', '=', 'gl_accounts.account_type_id')
                ->where('account_types.increase_action_is_debit', $request->is_debit);
        }

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = $query->paginate(Paginator::resolvePerPage());

        return GLAccountListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/finance/accounting-organizations/{accounting_organization_id}/gl-accounts",
     *      tags={"Finance"},
     *      summary="Create new GL Account",
     *      description="Create new GL Account. ``finance.gl_accounts.manage`` permission is required to perform
     *      this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="accounting_organization_id",
     *          in="path",
     *          required=true,
     *          description="Accounting organization identifier.",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateGLAccountRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Finance\CreateGLAccountRequest     $request
     * @param \App\Components\Finance\Models\AccountingOrganization $accountingOrganization
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateGLAccountRequest $request, AccountingOrganization $accountingOrganization)
    {
        $this->authorize('finance.gl_accounts.manage');
        $validated                               = $request->validated();
        $validated['accounting_organization_id'] = $accountingOrganization->id;

        $model = GLAccount::create($validated);

        return GLAccountResponse::make($model, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/accounting-organizations/{accounting_organization_id}/gl-accounts/{gl_account_id}",
     *      tags={"Finance"},
     *      summary="Returns full information about GL Account",
     *      description="Returns full information about GL Account. **`finance.gl_accounts.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="accounting_organization_id",
     *          in="path",
     *          required=true,
     *          description="Accounting organization identifier.",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="gl_account_id",
     *          in="path",
     *          required=true,
     *          description="GL account identifier.",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @param int $accountingOrganizationId Accounting organization identifier.
     * @param int $glAccountId              GL Account identifier.
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $accountingOrganizationId, int $glAccountId)
    {
        $this->authorize('finance.gl_accounts.view');

        $glAccount = GLAccount::query()
            ->with('taxRate', 'accountType')
            ->where([
                'id'                         => $glAccountId,
                'accounting_organization_id' => $accountingOrganizationId,
            ])
            ->firstOrFail();

        return GLAccountResponse::make($glAccount);
    }

    /**
     * @OA\Patch(
     *      path="/finance/accounting-organizations/{accounting_organization_id}/gl-accounts/{gl_account_id}",
     *      tags={"Finance"},
     *      summary="Allows to update GL Account",
     *      description="Allows to update GL Account. **`finance.gl_accounts.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="accounting_organization_id",
     *          in="path",
     *          required=true,
     *          description="Accounting organization identifier.",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="gl_account_id",
     *          in="path",
     *          required=true,
     *          description="GL account identifier.",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateGLAccountRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountResponse")
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
     * @param UpdateGLAccountRequest $request
     * @param int                    $accountingOrganizationId Accounting organization identifier.
     * @param int                    $glAccountId              GL Account identifier.
     *
     * @return \App\Http\Responses\ApiOKResponse|\App\Http\Responses\Finance\GLAccountResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateGLAccountRequest $request, int $accountingOrganizationId, int $glAccountId)
    {
        $this->authorize('finance.gl_accounts.manage');

        $glAccount = GLAccount::query()
            ->with('taxRate', 'accountType')
            ->where([
                'id'                         => $glAccountId,
                'accounting_organization_id' => $accountingOrganizationId,
            ])
            ->firstOrFail();

        $glAccount->fillFromRequest($request);

        return GLAccountResponse::make($glAccount);
    }

    /**
     * @OA\Get(
     *      path="/finance/gl-accounts/search",
     *      tags={"Finance", "Search"},
     *      summary="Get filtered set of gl accounts.",
     *      description="Allows to filter gl accounts by location, accounting organization, account type,
     *          bank account. ``finance.gl_accounts.view`` permission is required to perform
     *      this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="gl_account_id",
     *         in="query",
     *         description="Allows to filter gl accounts by gl account identifier.",
     *         @OA\Schema(
     *          description="GL account identifier.",
     *          type="integer",
     *          example="1",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="account_type_id",
     *         in="query",
     *         description="Allows to filter gl accounts by account type identifier.",
     *         @OA\Schema(
     *          description="Account type identifier.",
     *          type="integer",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="locations[]",
     *         in="query",
     *         description="Allows to filter GL accounts by location identifiers.",
     *         @OA\Schema(
     *              type="array",
     *              @OA\Items(
     *                  type="integer",
     *                  example=1
     *              ),
     *         )
     *      ),
     *      @OA\Parameter(
     *          name="is_debit",
     *          in="query",
     *          description="Allows to filter by account type",
     *          @OA\Schema(
     *             type="boolean",
     *          )
     *      ),
     *      @OA\Parameter(
     *         name="accounting_organization_id",
     *         in="query",
     *         description="Allows to filter gl accounts by accounting organization identifier.",
     *         @OA\Schema(
     *            type="integer",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="is_bank_account",
     *         in="query",
     *         description="Allows to filter gl accounts by specified flag.",
     *         @OA\Schema(
     *            type="boolean",
     *            example=false
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="enable_payments_to_account",
     *         in="query",
     *         description="Allows to filter gl accounts by specified flag.",
     *         @OA\Schema(
     *            type="boolean",
     *            example=false
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/GLAccountSearchListResponse")
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
     * )
     *
     * @param \App\Http\Requests\Finance\SearchGLAccountRequest $request
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function search(SearchGLAccountRequest $request)
    {
        $this->authorize('finance.gl_accounts.view');

        $filter = new GLAccountFilter($request->validated());

        $query = GLAccountView::filter($filter);

        return new GLAccountSearchListResponse($query->get());
    }
}
