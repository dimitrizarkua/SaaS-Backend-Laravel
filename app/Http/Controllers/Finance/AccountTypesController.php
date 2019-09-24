<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\AccountType;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\CreateAccountTypeRequest;
use App\Http\Requests\Finance\UpdateAccountTypeRequest;
use App\Http\Responses\Finance\AccountTypeListResponse;
use App\Http\Responses\Finance\AccountTypeResponse;

/**
 * Class AccountTypesController
 *
 * @package App\Http\Controllers\Finance
 */
class AccountTypesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/finance/account-types",
     *      tags={"Finance"},
     *      summary="Get list of account types",
     *      description="Returns list of account types. **`finance.gl_accounts.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountTypeListResponse"),
     *       ),
     *     )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('finance.gl_accounts.manage');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = AccountType::paginate(Paginator::resolvePerPage());

        return AccountTypeListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/finance/account-types",
     *      tags={"Finance"},
     *      summary="Create new account type",
     *      description="Create new account type. **`finance.gl_accounts.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateAccountTypeRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountTypeResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateAccountTypeRequest $request)
    {
        $this->authorize('finance.gl_accounts.manage');
        $accountType = AccountType::create($request->validated());
        $accountType->saveOrFail();

        return AccountTypeResponse::make($accountType, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/finance/account-types/{id}",
     *      tags={"Finance"},
     *      summary="Returns full information about account type",
     *      description="Returns full information about account type. **`finance.gl_accounts.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountTypeResponse")
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
    public function show(AccountType $accountType)
    {
        $this->authorize('finance.gl_accounts.manage');

        return AccountTypeResponse::make($accountType);
    }

    /**
     * @OA\Patch(
     *      path="/finance/account-types/{id}",
     *      tags={"Finance"},
     *      summary="Allows to update account type",
     *      description="Allows to update account type. **`finance.gl_accounts.manage`** permission
    is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateAccountTypeRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/AccountTypeResponse")
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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateAccountTypeRequest $request, AccountType $accountType)
    {
        $this->authorize('finance.gl_accounts.manage');
        $accountType->fillFromRequest($request);

        return AccountTypeResponse::make($accountType);
    }
}
